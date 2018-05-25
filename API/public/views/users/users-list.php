<?php self::_render( 'components/page-header', [ 
                            'title'             =>'Utilisateurs', 
                            'search-display'    =>true,
                            'search-action'     =>SITE_URL . '/users/users',
                            'search-value'      =>$datas->searchfield
                        ] ); ?>

<div class="row">
    <div class="col-md-12">

        <section>
            
            <?php self::_render( 'components/section-toolsheader', [ 
                                'tool-add' => true,
                                'tool-add-right' => 'add',
                                'tool-add-url' => '/users/userform/',
                                'tool-add-label' => 'Ajouter un utilisateur',
                                'rightpage' => 'users',
                                'response' => $datas->response
                            ] ); ?>
            <div class="body-section"> 
               
            <?php
            if( isset( $datas->datas ) )
            {
                foreach( $datas->datas as $n => $data )
                { 
                    ?>
                    <section class="profile clearfix" id="<?php echo $data->IdUser; ?>">
                        <?php self::_render( 'components/section-toolsheader', [ 
                                            'title' => $data->LastnameUser.' '.$data->FirstnameUser,
                                            'subtitle' => $data->NameGroup, 
                                            'tool-update' => true,
                                            'tool-update-url' => '/users/userform/' . $data->IdUser,
                                            'tool-delete' => true,
                                            'tool-delete-url' => '/users/userdelete/' . $data->IdUser,
                                            'tool-delete-display' => !$data->infos['hasDependencies'],
                                            'rightpage'=>'users',
                                            'alertbox-display' => false
                                        ] ); ?>
                    </section>
                    <?php
                }
            }
            else
            {
                ?>
                <p class="alert alert-info">Aucun utilisateur !</p>
                <?php
            }
        ?>
        </div>

        <?php self::_render( 'components/window-modal', [ 
                            'idname'=>'delete', 
                            'title'=>'Suppression de contenus', 
                            'content'=>'Etes-vous sûr de vouloir supprimer ce contenu ?', 
                            'submitbtn' => 'Supprimer' 
                        ] ); ?>
        </section>
    </div>
</div>