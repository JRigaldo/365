<?php  
namespace applications\users;

use includes\components\CommonModel;

use includes\tools\Orm;
use includes\tools\Date;
use includes\Request;
  
/**
 * class Model
 * 
 * Filters apps datas
 *
 * @param array $_beneficiaire  | Table and fields structure "users".
 *                  
 */
class ModelUsers extends CommonModel {     
    
    
    function __construct() 
    {
        $this->_setTables(['users/builders/BuilderUsers']);
        
        $this->_setModels([ 'users/ModelGroups' ]);
        
    }

    /**
     * Select datas form the table "users"
     * 
     * @param array $params | (optional) Conditions [ 'Field'=>value ]
     * @param str   $period | (optional) Period or state depending on value choosed
     *                        ('all', 'archive', 'actual', 'future', 'cancel', search, or integer(year-YYYY))
     *                        'all' by default
     * @param array $groups | (optional) Group(s) Type ('participants' or 'manager') or 'all' (for all groups)
     * @return array        | Results of the selection in the database.
     */
    public function users( $params = [] )
    {
        $orm = new Orm( 'users', $this->_dbTables['users'], $this->_dbTables['relations'] );

        $result = $orm    ->select()
                ->joins( ['users'=>['groups']] )
                ->where( $params )
                ->execute( true );
        
        return $result;
    }
    
    
    /**
     * Prepare datas for the formulas 
     * depending on the table "beneficiaire".
     * Manage sending. Returns settings datas and errors
     * 
     * @param int $id       | (optional) Id of the content. 
     * @return object       | Datas and errors.
     */   
    public function userBuild( $id = null )
    {
        $orm = new Orm( 'users', $this->_dbTables['users'] );
            
        $orm->prepareGlobalDatas( [ 'POST' => true ] );
        
        $params = ( isset( $id ) ) ? ['IdUser' => $id] : null;
            
        return $orm->build( $params );
    }
    
    /**
     * Updates datas in the database.
     * Do insert and update.
     * Figure errors and send back false in that case
     * 
     * @param string $action  | (optionnal) Action to do.
     *                          Default : does insert.
     *                          Defined by "insert" or "update". 
     * @param int $id         | (optional) Id of the content to update.
     *                          It is mandatory for updates.
     * @return boolean|object | false when errors are found 
     *                          (ex. empty fields, bad file format imported,...). 
     *                          Object with content datas when process went good. 
     */ 
    public function userUpdate( $action = 'insert', $id = null) 
    {
        $orm        = new Orm( 'users', $this->_dbTables['users'] );
        
        $datas = $orm->prepareGlobalDatas( [ 'POST' => true ] );

        if( !$orm->issetErrors() )
        {
            if( $action === 'insert' )
            {
                $request        = Request::getInstance();
                $newpassword    = $request->genToken( 3 );
                        
                $orm->prepareDatas([ 'PassUser' => $newpassword, 'PseudoUser' => $datas['EmailUser'] ]);
             
                $data = $orm->insert();                
                
                $id = $data->IdUser;
            }
            else if( $action === 'update' )
            {
                $data = $orm->update([ 'IdUser' => $id ]);
            }    
            
            return $data;
        }
        else
        {
            return false;
        }
    }

    
    /**
     * Delete an entry in the database.
     * 
     * @param int $id   | Id of the content to delete.
     * @return boolean  | Return's true in all cases.    
     */
    public function userDelete( $id ) 
    {
        $orm = new Orm( 'users', $this->_dbTables['users'] );
            
        $orm->delete([ 'IdUser' => $id ]);
        
        return true;
    } 
    
    
    
    
    public function usersApiDatas( $params ) 
    {
        $usersApi = [];

        $users = $this->users( $params );

        if( isset( $users ) )
        {
            foreach( $users as $u )
            {
                $fields = [];

                foreach( $u as $prop => $value )
                {
                    if( is_string( $value ) && $prop !== 'PassUser' )
                    {
                        $fields[ $prop ] = $value;
                    }
                }
                $usersApi[]  = $fields;
            }
        }
        
        return $usersApi;
    } 

    
    public function stats( $params, $paramGreater = [], $paramLower = [] )
    {
        $orm = new Orm( 'users_statistics', $this->_dbTables['users_statistics'] );

        $results = $orm ->select()
                        ->where( $params )
                        ->wheregreaterandequal( $paramGreater )
                        ->wherelowerandequal( $paramLower )
                        ->order([ 'DateStatistic' => 'ASC' ])
                        ->execute();
        
        return $results;
    }
    
    
    public function statsApiDays( $params, $days = 'all' ) 
    {
        $statsApi       = [];
        
        $paramGreater   = ( $days !== 'all' ) ? [ 'DateStatistic' => $days . ' 00:00:00' ] : [];
        
        $paramLower     = ( $days !== 'all' ) ? [ 'DateStatistic' => $days . ' 23:59:59' ] : [];
        
        $results = $this->stats( $params, $paramGreater, $paramLower );
        
        if( isset( $results ) )
        {
            foreach( $results as $result )
            {
                if( !empty( $result->DateStatistic ) && $result->DateStatistic !== '0000-00-00 00:00:00' )
                {
                    $date = new Date( $result->DateStatistic );

                    $dayDateHyphen = $date->get_date_hyphen( 'DD-MM-YYYY' );

                    if( !isset( $dayDateHyphen ) ) 
                    {
                        $statsApi[ $dayDateHyphen ] = [];
                    }

                    $statsApi[ $dayDateHyphen ][] = [ 'date'=>$date->get_date(), 'hour'=>$date->get_time(), 'eval'=>$result->ValueStatistic ];
                }
            }
        }
        
        return $statsApi;
    } 

    /**
     * 
     * @param array $params
     * @param integer $month
     * @return array
     */
    public function statsApiMonth( $params, $month, $year ) 
    {
        $statsApi = [];
        
        $dateEndTimestamp = mktime( '00', '00', '00', ( $month + 1 ), '01', $year );
        
        $dayEndMonth = date( 'd', ($dateEndTimestamp - ( 3600 * 24 ) ) );
        
        for( $i = 1; $i <= $dayEndMonth; $i++ )
        {
            $day = ( $i <= 9 ) ? '0'.$i : $i;
            
            $statsApi[ $year . '-' . $month . '-' . $day ] = $this->statsApiDays( $params, $year . '-' . $month . '-' . $day );
        }
        
        return $statsApi;
    } 

    

    public function statsApiWeek( $params, $week, $year )
    {
        $statsApi = [];
        
        $dateStartTimestamp = strtotime( sprintf("%4dW%02d", $year, $week ) ); // Start of the week
        
        $dayLength = ( 3600 * 24 );
        
        $limit = $dateStartTimestamp + ( 7 * $dayLength );
        
        for( $i = $dateStartTimestamp; $i <= $limit; $i += $dayLength )
        {
            $date = date( 'Y-m-d', $i );
            
            $statsApi[ $date] = $this->statsApiDays( $params, $date );
        
        }
        
        return $statsApi;
    }

    /**
     * 
     * @param type $params
     * @param type $year
     * @param type $returnFormat 'weeks' or 'months'
     * @return type
     */
    public function statsApiYear($params, $year, $returnFormat)
    {
        return [];
    }

    /**
     * 
     * @param type $params
     * @param type $from
     * @param type $to
     * @param type $returnFormat 'days', 'weeks' or 'months'
     * @return type
     */
    public function statsApiFromto($params, $from, $to, $returnFormat)
    {
        return [];
    }
    
    /**
     * 
     * @param type $IdUSer
     * @param type $eval
     * @param integer $datetime timestamp
     */
    public function statsApiInsert($IdUSer, $eval, $timestamp)
    {
        $usersApi = [];
        
        $time = $timestamp - 300;

        $dateStatisticLimit = date( 'Y-m-d H:i:s', $time );
        
        $results = $this->stats( ['IdUser' => $IdUSer ], [ 'DateStatistic' => $dateStatisticLimit ] );
        
        if( !isset( $results ) )
        {
            $dateStatistic = date( 'Y-m-d H:i:s', $timestamp );

            $orm = new Orm( 'users_statistics', $this->_dbTables['users_statistics'] );
            
            $datas = ['IdUser'=>$IdUSer, 'ValueStatistic'=>$eval, 'DateStatistic'=>$dateStatistic ];
                        
            $orm->prepareDatas($datas);

            $data = $orm ->insert();
            
            if( isset( $data ) )
            {
                foreach( $data as $prop => $value  )
                {
                    if( $prop != 'field')
                    {
                        $usersApi[ $prop ] = $value;
                    }
                }
            }
        }
        
        return $usersApi;
            
    }
    
}