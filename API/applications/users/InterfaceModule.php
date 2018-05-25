<?php
namespace applications\users;

use includes\components\Module;

/**
 * This file is mandatory to the module
 * It's class is automaticaly loaded in the Controller 
 * by the $this->_interface property
 */
class InterfaceModule extends Module
{
    private $_grouphead;

    public function __construct()
    {
        $this->_grouphead = [ 'cells' => [
            [ 'title' => '#',           'colspan' => '1', 'class' => 'cell-mini' ],
            [ 'title' => 'Groupe',      'colspan' => '1', 'class' => 'cell-small' ],
            [ 'title' => 'Description', 'colspan' => '1', 'class' => 'cell-xxlarge' ],
            [ 'title' => 'Modifier',    'colspan' => '1', 'class' => 'cell-small', 'right' => 'update', 'rightmenu' => 'menus', 'rightaction' => '' ],
            [ 'title' => 'Supprimer',   'colspan' => '1', 'class' => 'cell-small', 'right' => 'delete', 'rightaction' => '' ]
        ] ];
    }   
    
    public function getGroupHead()
    {
        return $this->_grouphead;
    }
    
    public function getUpdatedGroupDatas( $urlDatas )
    {
        $updatemessage  = '';
        
        $msgDatas = $this->_updatedMsgDatas( $urlDatas, 'users/ModelGroups/groups', 'IdGroup', 'NameGroup' );

        if( $msgDatas[ 'updated' ] )
        {
            $updatemessage .= ( $msgDatas[ 'action' ] === 'successinsert' ) ? 'Le groupe <strong>'.$msgDatas[ 'updatedname' ] . '</strong> vient d\'être ajouté.' : '';

            $updatemessage .= ( $msgDatas[ 'action' ] === 'successupdate' ) ? 'Le groupe <strong>'.$msgDatas[ 'updatedname' ] . '</strong> vient d\'être mis à jour.' : '';

            $updatemessage .= ( $msgDatas[ 'action' ] === 'successdelete' ) ? 'Un groupe vient d\'être supprimée.' : '';
            
        }
        
        return [ 'updated' => $msgDatas[ 'updated' ], 'updatemessage' => $updatemessage, 'updateid' => $msgDatas[ 'updatedid' ] ];
    }

    
    
    
    
    
    
    public function getGroups( $params = [] )
    {        
        $this->_setModels(['users/ModelGroups']);
        
        $groups = $this->_models['ModelGroups']->groups( $params );

        $groupList = [];

        if( is_array( $groups ) )
        {
            foreach( $groups as $group )
            {             
                $groupList[] = ['value' => $group->IdGroup, 'label'=>$group->NameGroup ];
            }
        }
        
        return $groupList;    
    }
   
    
    
    
    /**
     * Defines what info is sent back to the uset after an  
     * interaction (insert, update or delete) has been mad with the database.
     * 
     * @param str $urlDatas     | Last part of the Url (Router).
     * @return array            | Return's infos to display :
     *                            'updated'       | boolean   If an interaction has been made
     *                            'updatemessage' | str       Message content
     *                            'updatedid'     | int       Id of the content inserted, updated or deleted
     */
    public function getUserUpdatedDatas( $urlDatas )
    {
        $updatemessage  = '';

        $alert          = 'success'; // 'success', 'info', 'warning', 'danger' 
        
        $msgDatas = $this->_updatedMsgDatas( $urlDatas, 'users/ModelUsers/users', 'IdUser', 'FirstnameUser', 'LastnameUser' );

        if( $msgDatas[ 'updated' ] )
        {
            $updatemessage .= ( $msgDatas[ 'action' ] === 'successinsert' ) ? 'Le profil de <strong><a href="#'.$msgDatas[ 'updatedid' ].'">'.$msgDatas[ 'updatedname' ] . '</a></strong> vient d\'être ajouté.' : '';

            $updatemessage .= ( $msgDatas[ 'action' ] === 'successupdate' ) ? 'Le profil de <strong><a href="#'.$msgDatas[ 'updatedid' ].'">'.$msgDatas[ 'updatedname' ] . '</a></strong> vient d\'être mis à jour.' : '';

            $updatemessage .= ( $msgDatas[ 'action' ] === 'successdelete' ) ? 'Le profil vient d\'être supprimé.' : '';
            
        }
        
        return [ 'updated' => $msgDatas[ 'updated' ], 'updatemessage' => $updatemessage, 'updateid' => $msgDatas[ 'updatedid' ], 'alert' => $alert ];
    }
    

    /**
     * Defines what info is sent back to the uset after an  
     * interaction (insert, update or delete) has been mad with the database.
     * 
     * @param str $build     | Build datas for froms.
     * @return array            | Return's infos to display :
     *                            'updated'       | boolean   If an interaction has been made
     *                            'updatemessage' | str       Message content
     *                            'updatedid'     | int       Id of the content inserted, updated or deleted
     */
    public function getUserFormUpdatedDatas( $build )
    {
        $updatemessage  = '';

        $alert          = 'success'; // 'success', 'info', 'warning', 'danger' 
        
        $updated        = false;
        
        if( isset( $build->errors ) )
        {
            $updatemessage = 'Certains champs ont été mal remplis.';
            $updated        = true;            
        }
        
        return [ 'updated' => $updated, 'updatemessage' => $updatemessage, 'updateid' => null, 'alert' => $alert ];
    }
    
    
    public function getMenus()
    {
        $this->_setModels( ['menus/ModelMenus' ] );
        
        $modelMenus     = $this->_models[ 'ModelMenus' ];
        
        $menus = $modelMenus->getAdminmenu();
        
        $menusList = [];
        
        foreach( $menus as $heading )
        {
            
            if( isset( $heading[ 'menus' ] ) )
            {
                $menusItems = [];
                
                foreach( $heading[ 'menus' ] as $n => $menu )
                {
                    $menusItems[] = [ 'label' => $menu->NameMenu, 'value' => $menu->IdMenu ];
                }
                
                $menusList[] = [ 'name' => $heading[ 'label' ], 'options' => $menusItems ];
            }
        }
            
        return $menusList;
    }   
    
  
}