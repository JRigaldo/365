<?php
namespace applications\menus;

use includes\components\CommonController;
use includes\Request;

use stdClass;

class Controller extends CommonController{

    private function _setmenuForm()
    {     
        $this->_setModels( ['menus/ModelMenus','menus/ModelModules' ] );
        
        $modelMenus     = $this->_models[ 'ModelMenus' ];
        $modelModules   = $this->_models[ 'ModelModules' ];
        
        $id = ( !empty( $this->_router ) ) ? $this->_router : null;

        $this->_datas = new stdClass;

        $this->_datas->form     = $modelMenus->adminmenuBuild( $id );

        $this->_datas->headers  = $modelMenus->getHeadings();

        $this->_datas->modules  = $modelModules->setModuleOptions();

        $this->_view = 'menus/menu-form';
    }

    private function _setmodulesForm()
    {   
        $this->_setModels( ['menus/ModelMenus','menus/ModelModules' ] );
        
        $modelMenus     = $this->_models[ 'ModelMenus' ];
        $modelModules   = $this->_models[ 'ModelModules' ];
        
        $id = ( !empty( $this->_router ) ) ? $this->_router : null;

        $this->_datas = new stdClass;

        $this->_datas->tabs     = $this->_interface->getTabs( 'modules' );

        $this->_datas->form     = $modelModules->modulesBuild( $id );

        $this->_datas->response = $this->_interface->getModulesFormUpdatedDatas( $this->_datas->form );

        $this->_view = 'menus/modules-form';
    }
    
    
    protected function _setDatasView()
    {
        $this->_setModels( ['menus/ModelMenus','menus/ModelModules' ] );
        
        $modelMenus     = $this->_models[ 'ModelMenus' ];
        $modelModules   = $this->_models[ 'ModelModules' ];
        
        switch( $this->_action )
        {
        
            case 'config':
                
                $this->_view = 'menus/configmenu';
                
            break;
        
        
            case 'menuform':
                
                $this->_setmenuForm();
                
            break;
            
        
            case 'menuactiveAjax':
                
                $datas = new stdClass;
                
                if( $return = $modelMenus->adminmenuActiveUpdate( $this->_request->getVar( 'id' ) ) )
                {
                    echo json_encode([ 'token' => $_SESSION[ 'token' ], 'status' => 'OK', 'data' => $datas, 'msg' => 'La rubrique <strong>' . $return['name'] . '</strong> a été ' . ( ( $return['active'] === 1 ) ? 'activée.' : 'désactivée.') ]);
                }
                else
                {
                    echo json_encode([ 'token' => $_SESSION[ 'token' ], 'status' => 'FAIL', 'data' => $datas, 'msg' => '' ]); 
                }
                exit;
                
            break;
            
        
            case 'menuorderAjax':
                
                $datas = new stdClass;
                
                if( $modelMenus->adminmenuPosition( $this->_request->getVar( 'id' ) ) )
                {
                    echo json_encode([ 'token' => $_SESSION[ 'token' ], 'status' => 'OK', 'data' => $datas, 'msg' => '' ]); 
                }
                else
                {
                    echo json_encode([ 'token' => $_SESSION[ 'token' ], 'status' => 'FAIL', 'data' => $datas, 'msg' => '' ]);   
                }
                exit;
                
            break;
            
            
            case 'menuupdate':
                
                $id     = ( !empty( $this->_router ) ) ? $this->_router : null;
                $action = ( !empty( $this->_router ) ) ? 'update' : 'insert';
                
                if( $data = $modelMenus->adminmenuUpdate( $action, $id ) )
                {
                    header( 'location:' . SITE_URL . '/menus/menulist/success' . $action . '/' . $data->IdMenu );
                    
                    exit;
                }
                else 
                {
                    $this->_setmenuForm();
                }
            break;
            
            
            case 'menudeleteAjax':
                
                $datas = new stdClass;

                if( $this->_datas = $modelMenus->adminmenuDelete( $this->_request->getVar( 'id' ) ) )
                {
                    echo json_encode([ 'token' => $_SESSION[ 'token' ], 'status' => 'OK', 'data' => $datas, 'msg' => 'Une rubrique vient d\'être supprimée.' ]); 
                }
                else
                {
                    echo json_encode([ 'token' => $_SESSION[ 'token' ], 'status' => 'FAIL', 'data' => $datas, 'msg' => '' ]);   
                }
                
                exit;
                
            break;
        
            
            
            // MODULES
               
            case 'modules':
                
                $this->_datas = new stdClass;
                
                $this->_datas->tabs         = $this->_interface->getTabs( 'modules' );
                
                $this->_datas->datas        = $modelModules->modules();
                
                $this->_datas->response     = $this->_interface->getModulesUpdatedDatas( $this->_router );
                
                $this->_datas->tableHead    = $this->_interface->getModulesTableHead();
                
                $this->_view = 'menus/modules-list';
                
            break;
            
            case 'modulesform':
                
                $this->_setmodulesForm();
                
            break;
            
            case 'modulesupdate':
                
                $id     = ( !empty( $this->_router ) ) ? $this->_router : null;
                $action = ( !empty( $this->_router ) ) ? 'update' : 'insert';
                
                if( $data = $modelModules->modulesUpdate( $action, $id ) )
                {
                    header( 'location:' . SITE_URL . '/menus/modules/success' . $action . '/' . $data->IdModule );
                    
                    exit;
                }
                else 
                {
                    $this->_setmodulesForm();
                }
            break;
            
            
            case 'modulesdeleteAjax':
                
                $datas = new stdClass;

                if( $this->_datas = $modelModules->modulesDelete( $this->_request->getVar( 'id' ) ) )
                {
                    echo json_encode([ 'token' => $_SESSION[ 'token' ], 'status' => 'OK', 'data' => $datas, 'msg' => 'Un module vient d\'être supprimé.' ]); 
                }
                else
                {
                    echo json_encode([ 'token' => $_SESSION[ 'token' ], 'status' => 'FAIL', 'data' => $datas, 'msg' => '' ]);   
                }
                
                exit;
                
            break;            
                    
            
            
            // MENU
        
            default:
                $this->_datas = new stdClass;
                
                $this->_datas->tabs         = $this->_interface->getTabs( '' );
                
                $this->_datas->response     = $this->_interface->getUpdatedDatas( $this->_router );
                
                $this->_datas->tableDatas   = $modelMenus->getAdminmenu();
                
                $this->_datas->tableHead    = $this->_interface->getTableHead();
                
                $this->_view = 'menus/menu-list';
            break;
        }
    }

}