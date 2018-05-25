<?php
namespace includes;

class Api{
    
    
    
    /**
     * Class constructor (Multi-Singleton).
     */
    private function __construct()
    {	
    }
    
    
    
    public static function apiUrl( $page, $action, $router )
    {
        if( $page === 'api' )
        {            
            header("Access-Control-Allow-Origin:*" );
            
            //if( isset( $_SERVER['HTTP_ORIGIN'] ) )
            //{
                //header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] );
                //header("Content-Type: text/plain" );
            //}
            
            $datas = Api::getDatas( $action, $router );
            
            $json = json_encode( $datas,  JSON_UNESCAPED_UNICODE );
            
            echo $json;
            
            Audit::setAudit( 'API called : '.$action.'/'.$router );
            
            exit;
        }
    }
    
    /**
     * Set a connection with a module Controller from wich datas are sent back
     * 
     * 
     * 
     * @param string $module   Module name called/used
     * @param string $params   Params sent to the module
     * @return array         datas
     */
    public static function getDatas( $module, $params )
    {
        include_once SITE_PATH . '/applications/' .$module. '/Controller.php';

        $controllerPath = '\applications\\'.$module.'\Controller'; // Acceder à la classe Controller par l'espace de nom.

        $controller = new $controllerPath( $module, 'api', $params );
                
        return $controller->datas();
    }
}