<?php
namespace includes;

use includes\Request;
use includes\tools\Orm;
use includes\tools\Mail;
use includes\tools\String;
use stdClass;


/**
 * Login class
 *
 * @author Olivier Dommange
 */
final class Login 
{    
    /**
     * All settings define in a array.
     *
     * @var array of configuration object.
     */
    private static $_datas;
    
    private static $_users;
    
    private static $_request;
    
    public static function initSettings()
    {  
        self::$_datas = new stdClass;
        
        self::$_datas->isLoguedIn = self::isLoguedIn();
        
        self::$_datas->isVisitor = self::isVisitor();
        
        self::$_users = [
            'IdUser'        => [ 'type' => 'INT', 'autoincrement' => true, 'primary' => true  ],
            'PseudoUser'    => [ 'type' => 'STR', 'mandatory' => true ],
            'PassUser'      => [ 'type' => 'STR', 'mandatory' => true ],
            'IdGroup'       => [ 'type' => 'STR' ],
            'LastnameUser'  => [ 'type' => 'STR' ],
            'FirstnameUser' => [ 'type' => 'STR' ],
            'EmailUser'     => [ 'type' => 'STR', 'mandatory' => true ],
            'PhoneUser'     => [ 'type' => 'STR' ],
            'AddressUser'   => [ 'type' => 'STR' ],
            'ZipCodeUser'   => [ 'type' => 'STR' ],
            'CityUser'      => [ 'type' => 'STR' ]
        ];
        
        self::$_request = Request::getInstance();   
        
        if( self::$_request->getVar( 'adminuser' ) !== null )
        {
            if( self::_login( self::$_request->getVar( 'adminuser' ) ) ) 
            {
                header('location: ' . SITE_URL . '/home'); exit;
            }
        }
    }
    
    
    public static function getDatas()
    {
        return self::$_datas;
    }
    
    
    /**
     * Defines the landing page after connection and
     * redirect user to this page
     */
    public static function landingPage()
    {
        $orm = new Orm('groups');
        
        $result = $orm  ->select()
                        ->join([ 'groups'=>'IdMenuLanding', 'adminmenus'=>'IdMenu' ])
                        ->where([ 'IdGroup' => $_SESSION['IdGroup'] ])
                        ->first();
        
        if( isset( $result ) && $result->IdMenuLanding > 0 )
        {
            $ormModule = new Orm('adminmenumodules');
            
            $resultModule = $ormModule  ->select()
                                        ->where([ 'IdModule' => $result->ModuleMenu ])
                                        ->first();
            
            if( isset( $resultModule ) )
            {
                header('location:' . SITE_URL . '/' . $resultModule->NameModule . '/' . $result->ActionMenu );
                
                exit;
            }
        }
        else
        {            
            $headings   = Adm::getHeadings();
            
            if( isset ( $headings ) )
            {                
                foreach( $headings as $heading )
                {
                    $orm = new Orm( 'adminmenus' );

                    $results = $orm ->select()
                                    ->where(['HeadingMenu'=>$heading['value']])
                                    ->order([ 'OrderMenu' => 'ASC' ])
                                    ->first();
                    
                    if( isset( $results ) )
                    {
                        echo $results->ModuleMenu;
                        $ormModule = new Orm( 'adminmenumodules' );

                        $resultModule = $ormModule  ->select()
                                                    ->where([ 'IdModule' => $results->ModuleMenu ])
                                                    ->first();

                        if( isset( $resultModule ) )
                        {
                            header('location:' . SITE_URL . '/' . $resultModule->NameModule . '/' . $results->ActionMenu );

                            exit;
                        }
                    }
                }
            }
        }
    }
        
    
    /**
     * Prepare datas in the format that permits a login by URL to a specific page.
     * Checks also that the login wil ba allowed. If it's not the case returns FALSE.
     * This beacause the user group don't have the right to read the page.
     * 
     * @param string $nameModule  Name of the module. Should be an accessible page to this user.
     *                            Could be 'password' in the case it is used to let the user change his 'password'
     *                            No pages acces wil be checked in that case.
     * @param string $emailUser   
     * $param string $moduledata (optional) Adds datas to the url. For login is crypted with _userCryptPass()
     * 
     * @return string|false     Cripted URL. False if access not allowed.
     */
    public static function tokenizerLoginUrl( $nameModule, $emailUser, $moduledata = '' )
    {
        $ormUser = new Orm('users');

        $user = $ormUser   ->select()
                            ->where([ 'EmailUser' => $emailUser ])
                            ->first();
        
        if( isset( $user ) )
        {            
            $ormGroups = new Orm('group_rights');
            
            $result = $ormGroups    ->select()
                                    ->join([ 'group_rights'=>'IdMenu', 'adminmenus'=>'IdMenu' ])
                                    ->join([ 'adminmenus'=>'ModuleMenu', 'adminmenumodules'=>'IdModule' ])
                                    ->where([ 'NameModule' => $nameModule, 'IdGroup' => $user->IdGroup, 'Rights' => 'r' ])
                                    ->first();  
            
            if( isset( $result ) || $nameModule === 'login' )
            {
                return self::_userCryptPass( $nameModule . '-' . $emailUser ) . ( ( !empty( $moduledata ) ) ? '-' . self::_userCryptPass( $moduledata ) : '' );
            }
        }
        
        return false;
    }
    
    
    /**
     * Found data correspondance that has been sent in the url login process to a page
     * It is compared to datas sent in an array.
     * 
     * @param string $token Sent from the url login process (set by the Logn::tokenizerLoginUrl() method)
     * @param array $datas  Datas in a single layout array
     * 
     * @return string|false The value found from the array. False if nothing found
     */
    public static function foundModuleDatas( $token, array $datas )
    {
        if (is_array( $datas ) )
        {
            foreach( $datas as $data )
            {
                if( $token === self::_userCryptPass( $data ) )
                {
                    return $data;
                }
            }
        }
        
        return false;
    }
    
    
    /**
     * Do the process to check if the url crypted has a correspondant page and user
     * 
     * Multiple checks are done (in this order) :
     * 1. The token has two strings (tokens) merged in one
     * 2. Checks if the page and e-mail user is correspondant
     * 3. Checks that the user group has the right to access this page
     * 
     * @param string $tokensString Two tokens merged in one string.
     *                             First token contains page and user e-mail
     *                             Second token has specific crypted datas to give to the page
     * 
     * @return object | null 
     */
    private static function _checkLoginByUrl( $tokensString )
    {
        $tokensString = str_replace( ' ', '', $tokensString );
        
        $tokensArray = explode( '-', $tokensString );
                
        if( count( $tokensArray ) === 2 )
        {
            $ormPage = new Orm('adminmenus');

            $pages = $ormPage   ->select()
                                ->join(['adminmenus'=>'ModuleMenu', 'adminmenumodules'=>'IdModule'])
                                ->execute();
            
            $pagePassword = new stdClass();
            
            $pagePassword->NameModule = 'login';
            
            array_push( $pages, $pagePassword );
            
            $ormUser = new Orm('users');

            $users = $ormUser   ->select()
                                ->execute();
            
            if( isset( $users ) )
            {
                $ormGroups = new Orm('group_rights');
                
                foreach( $users as $user )
                {     
                    foreach ( $pages as $page )
                    {
                        $tokenized = self::tokenizerLoginUrl( $page->NameModule, $user->EmailUser );
                        
                        if( !empty( $tokenized ) && $tokenized === $tokensArray[ 0 ] )
                        {   
                            $result = $ormGroups->select()
                                            ->where([ 'IdMenu' => $page->IdMenu, 'IdGroup' => $user->IdGroup, 'Rights' => 'r' ])
                                            ->first();  
                            
                            if( isset( $result ) || $page->NameModule === 'login' )
                            {                                
                                $user->NameModule = $page->NameModule;
                                
                                $user->routerToken = $tokensArray[ 1 ];

                                return $user;
                            }
                        }
                    }
                }
            }
        }
        return null;
    }
    
    
    /**
     * Checks if a login is attempted through the url
     * If the operation succeed the user will be 
     * redirected to the dedicated page and a session is started.
     * 
     * Will give access to one and only page to make 
     * a limited action (fill a form, change his password,...).
     * The user wil be considered as a visitor.
     * A session variable $_SESSSION['visitor'] is set to TRUE.
     * 
     * To get through this process the user must exist in the db
     * A redirection will systematicly be fixed on the page module found 
     * (which is defined in the router of the url).
     * The page module found must be an existing page and user group should be allowed to read this page.
     * The page module found can be 'password' in case the user has to set/change/redefine his password.
     * 
     * On the redirection the specific 'private' action is set to all redirection made after login. 
     * There is no way to change this. Don't try it's useless...
     * 
     * @param string $page    Must be 'login'
     * @param string $action  Must be 'eval'
     * @param string $router  Must be a string crypted by the _userCryptPass() method
     *                        It contains on one half the page and e-mail user.
     *                        On the other half is a crypted script send to the module.
     *                        The page found must be an existing page and user group should 
     *                        be allowed to read this page or be 'password'
     *                        If a match is found the redirection will systematicly be
     *                        fixed on the page found with the specific 'private' action.
     *                        Cannot contains any further informations (seprated by a 'slash' /)
     * 
     * @return void
     */
    public static function loginByUrl( $page, $action, $router )
    {
        $r = explode( '/', $router );
        
        if( $page === 'login' && $action === 'eval' && isset( $r[0] ) && !isset( $r[1] ) )
        {
            $userLogInfos = self::_checkLoginByUrl( $r[0] );
            
            if( isset( $userLogInfos ) )
            {
                self::_setUserSession( $userLogInfos, $userLogInfos->NameModule );
                
                Audit::setAudit([ 'Description' => 'Login By Url for ' . $userLogInfos->FirstnameUser . ' ' . $userLogInfos->LastnameUser ]);
                
                header( 'location:' . SITE_URL . '/'. $userLogInfos->NameModule .'/private/' . $userLogInfos->routerToken ); exit;
            }
        }
    }
    
    
    /**
     * Checks the $_SESSION[ 'isVisitor' ] and return its value. 
     * False if the session variable is not set or false
     * 
     * @return string|false
     */
    public static function isVisitor()
    {
        return ( isset( $_SESSION[ 'isVisitor' ] ) ) ? $_SESSION[ 'isVisitor' ] : false;
    } 
    
    
    /**
     * Used to allowed user to access the one and only page that he was connected for
     * from the url login.
     * It checks the $_SESSION[ 'isVisitor' ], which contains the page and action allowed.
     * The $_SESSION[ 'isVisitor' ] property is set when the login by url is made.
     * This method is used by the Template class to disconnect user if access denied
     * 
     * @param string $page    Page to check.
     * @param string $action  Action to check. Should be the string 'private'
     * 
     * @return boolean      Indicates if the access is allowed
     */
    public static function isVisitorAccess( $page, $action )
    {
        $isAccessAccepted = true;
        
        if( self::isVisitor() )
        {
            $isAccessAccepted = false;
            
            if( $action === 'private' && self::isVisitor() === $page )
            {
                $isAccessAccepted = true;
            }            
        }
        return $isAccessAccepted;
    }
        
    
    public static function checkCookieConnected( $page, $action )
    {
        if( isset( $_COOKIE[ SITE_COOKIE ] ) )
        {
            $Orm = new Orm( 'users', self::$_users );
            
            $user = $Orm    ->select()
                            ->where([ 'users.IdUser' => $_COOKIE[ SITE_COOKIE ] ])
                            ->first();
            
            if( isset( $user ) )
            {
                self::_setUserSession( $user, false );
                
                header( 'location:' . SITE_URL . '/' .$page . '/' . $action ); exit;
            }
        }
    }
    
    
    public static function userInfos()
    {
        $userInfos = null;
        
        if( self::isLoguedIn() )
        {
            $Orm = new Orm( 'users', self::$_users );
            
            $userInfos = $Orm   ->select()
                                ->where([ 'users.IdUser'=>$_SESSION[ 'IdUser' ] ])
                                ->first();
            
            $userInfos->isVisitor = self::isVisitor();
        }
        
        return $userInfos;
        
    }
    
    
    private static function _userCryptPass( $string )
    {
        return substr( md5( 'extranet'.$string ), 0, -12 );
    }
    
    
    public static function isLoguedIn()
    {
        return ( isset( $_SESSION[ 'adminOK' ] ) ) ? true : false;
    } 
    
    
    private static function _getErrors()
    {
        return isset( self::$_datas->errors ) ? self::$_datas->errors : null;
    }
    
    
    private static function _setErrors( $errors )
    {
        self::$_datas->errors = $errors;
    }
    
    
    public static function passchange()
    {
        $Orm    = new Orm( 'users', self::$_users );
        
        $password1 = self::$_request->getVar( 'password1' );
        
        $password2 = self::$_request->getVar( 'password2' );
        
        if( strlen( $password1 ) >= 6 )
        {
            if( $password1 === $password2 )
            {   
                if( isset( $_SESSION['IdUser'] ) )
                {
                    $Orm->prepareDatas(['PassUser' => self::_userCryptPass( $password1 ) ] );

                    $Orm->update([ 'IdUser' => $_SESSION['IdUser'] ]);
                
                    echo json_encode([ 'token' => $_SESSION[ 'token' ], 'status' => 'OK', 'alertsuccess' => ['newpass' => 'Votre mot de passe vient d\'être changé. <br><strong>Vous pouvez vous connecter</strong>.'], 'data' =>self::$_datas  ]); 

                    exit;
                }
                else
                {
                    echo json_encode([ 'token' => $_SESSION[ 'token' ], 'status' => 'FAIL', 'errors' => ['passerror' => 'Malheureusement le délai pour définir un nouveau mot de passe est échu. Veuillez faire une nouvelle demande de changement de mot de passe.'], 'data' =>self::$_datas  ]); 

                    exit; 
                }
            }
            else
            {
                echo json_encode([ 'token' => $_SESSION[ 'token' ], 'status' => 'FAIL', 'errors' => ['passerror' => 'Les deux mots de passe indiqués ne sont pas les mêmes.'], 'data' =>self::$_datas  ]); 
                
                exit;
            }
        }
        else
        {
            
        }            
        echo json_encode([ 'token' => $_SESSION[ 'token' ], 'status' => 'FAIL', 'errors' => ['passerror' => 'Le mot de passe doit contenir au moins 6 caractères.'], 'data' =>self::$_datas  ]); 

        exit;

    }
    
    public static function passrecovery()
    {
        $statut = 'FAIL';
        $Orm    = new Orm( 'users', self::$_users );
        $String = new String();
                
        $userEmail = self::$_request->getVar( 'adminemailrecover' );

        if( empty( $userEmail ) )
        {
            self::_setErrors( [ 'emailerror' => 'Veuillez remplir le champ.'  ] );
        }
        else if( !$String->check_format_string( $userEmail ) )
        {
            self::_setErrors( [ 'emailerror' => 'Veuillez indiquer une adresse e-mail valide.'  ] );
        }

        if( self::_getErrors() === null )
        {
            $user = $Orm    ->select()
                            ->where([ 'PseudoUser'=>$userEmail ])
                            ->whereor([ 'EmailUser'=>$userEmail ])
                            ->first();
   
            if( isset( $user ) )
            {
                $mail = new Mail();

                $message = 'Bonjour,<br />
                            Vous êtes invité à définir un nouveau mot de passe pour accéder à l\'outil :<br /><br />'.
                            '<a href="' . SITE_URL . '/login/eval/' . self::tokenizerLoginUrl( 'login', $user->EmailUser, $user->IdUser ).'"></a>'.
                            '<br /><br />Cordialement.<br /><br />' . SITE_TITLE . '(' . SITE_EMAIL . ')';
                $fromnom    = SITE_TITLE;
                $frommail   = SITE_EMAIL;
                
                $mail->sendSiteMail( $userEmail, 'Définir vos accès', $message, $fromnom, $frommail);

                //self::$_datas = utf8_encode($message);
            
                echo json_encode([ 'token' => $_SESSION[ 'token' ], 'status' => 'OK', 'alertsuccess' => ['newpass' => 'Un message vous permettant de définir un mot de passe vient de vous être envoyé par e-mail.'], 'data' =>self::$_datas  ]); 

                exit;
            }
            else 
            {
                self::_setErrors( [ 'emailerror' => 'Votre compte n\'a pas pu être identifié. Veuillez tenter de nouveau.'  ] );
            }
            
            echo json_encode([ 'token' => $_SESSION[ 'token' ], 'status' => $statut, 'errors' => self::_getErrors(), 'data' =>self::$_datas  ]); 
            
            exit;
        }
    }
    
    
    private static function _keepConnected()
    {
        setcookie( SITE_COOKIE, $_SESSION['adminId'], time() + ( 3600 * 24 * 90 ), '/', $_SERVER["HTTP_HOST"] );
    }
    
    
    private static function _setUserSession( $user, $isVisitor = 'password', $datas = '' )
    {
        $_SESSION['adminOK']        = true;
        $_SESSION['isVisitor']      = $isVisitor;
        $_SESSION['PseudoUser']     = $user->PseudoUser;
        $_SESSION['IdUser']         = $user->IdUser;
        $_SESSION['IdGroup']        = $user->IdGroup;			
        $_SESSION['LastnameUser']   = $user->LastnameUser;
        $_SESSION['FirstnameUser']  = $user->FirstnameUser;
        $_SESSION['EmailUser']      = $user->EmailUser;
        $_SESSION['DatasUser']      = $datas;
    }
    
    
    private static function _loguser( $userLogin, $userPass )
    {
        if( !self::$_datas->isLoguedIn )
        {
            $Orm    = new Orm( 'users' );
            $_userCryptPass  = self::_userCryptPass( $userPass );

            $user = $Orm   ->select()
                           ->where([ 'PseudoUser'=>$userLogin, 'PassUser'=>$_userCryptPass ])
                           ->whereorand([ 'EmailUser'=>$userLogin, 'PassUser'=>$_userCryptPass ])
                           ->first();
            
            if( isset( $user ) )
            {
                self::_setUserSession( $user, false );
                
                $request = Request::getInstance();
                
                if( $request->getVar( 'keepconnected' ) )
                {
                    self::_keepConnected();
                }
                
                Audit::setAudit([ 'Description' => 'Login SUCCESS' ]);
                
                header('location: ' . SITE_URL . '/home/loguedin' ); exit;

                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }
    
    
    private static function _login( $userLogin )
    {
        $request = Request::getInstance();
        
        if( ( ( $userPassword = $request->getVar( 'adminpass' ) ) ) !== null ) 
        {    
            $Orm    = new Orm( 'users' );

            if( empty( $userLogin ) )
            {
                $Orm->setErrors( ['PseudoUser'=>'empty'] );
            }
            
            if( empty( $userPassword ) )
            {
                $Orm->setErrors( ['PassUser'=>'empty'] );
            }
            
            if( !$Orm->issetErrors() )
            {
             
                if( !( self::_loguser( $userLogin, $userPassword ) ) )
                {
                    Audit::setAudit([ 'Login' => $userLogin, 'Description' => 'Login FAIL' ]);
                    
                    $Orm->setErrors( ['login'=>'fail'] );

                    self::_setErrors( $Orm->getErrors() );

                    return false;
                }
                else 
                {                    
                    return true;
                }
            }
            else
            {
                self::_setErrors( $Orm->getErrors() );
                
                return false;
            }
        }
        else 
        {
           return false;
        }
    }
    
    
    public static function logout()
    {
        if( isset( $_SESSION[ 'adminOK' ] ) ) 
        {
            Audit::setAudit([ 'Description' => 'LOGOUT' ]);
            
            unset( $_SESSION['adminOK'] );
            unset( $_SESSION['isVisitor'] );
            unset( $_SESSION['PseudoUser'] );
            unset( $_SESSION['IdUser'] );
            unset( $_SESSION['IdGroup'] );
            unset( $_SESSION['LastnameUser'] );
            unset( $_SESSION['FirstnameUser'] );
            unset( $_SESSION['EmailUser'] );
            unset( $_SESSION['DatasUser'] );
        }
        
	setcookie( SITE_COOKIE, ' ', time() - 3600, '/', $_SERVER["HTTP_HOST"] );
        
        header('location:' . SITE_URL ); exit; 
        
        return true;
    }
    
    
}
