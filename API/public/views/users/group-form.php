<?php self::_render( 'components/page-header', [ 
                            'title'             =>'Groupe', 
                            'backbtn-display'   =>true, 
                            'backbtn-url'       =>'/users/groups', 
                            'backbtn-label'     =>'Retour à la liste de groupes'
                        ] ); ?>

<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
     <section>
                  
    <?php self::_render( 'components/section-toolsheader', [ 
                                        'title'=>'Groupes',
                                        'subtitle'=>' - Modifier', 
                                        'tool-minified'=>true
                                    ] ); ?>

        <div class="clearfix">
            <br />

            <form action="<?php echo SITE_URL; ?>/users/groupupdate/<?php echo $datas->form->IdGroup; ?>" method="post" class="form-horizontal form-label-left">

                
                <?php /*self::_render( 'components/form-field', [ 
                                        'name'=>'IdGroup', 
                                        'type'=>'input-hidden', 
                                        'values'=>$datas->form, 
                                        'required'=>true 
                                    ] );*/ ?>
                
                <?php self::_render( 'components/form-field', [ 
                                        'name'=>'NameGroup', 
                                        'type'=>'input-text', 
                                        'values'=>$datas->form, 
                                        'title'=>'Nom du groupe', 
                                        'required'=>true 
                                    ] ); ?>
                
                
            <?php self::_render( 'components/form-field', [
                                'title'=>'Première page du menu', 
                                'name'=>'IdMenuLanding', 
                                'values'=>$datas->form, 
                                'type'=>'select-optgroup',
                                'options'=>$datas->menus,
                                'option-value'=>'value', 
                                'option-label'=>'label',
                                'first-option'=>'Retour à la page de connexion (aucun)',
                                'first-value'=>0
                            ] ); ?>
                
                
                
                
                <div class="form-group">
                    <div class="col-md-9 col-sm-9 col-xs-12 col-md-offset-3">
                        <button type="submit" class="btn btn-success">Envoyer</button>
                    </div>
                </div>

            </form>


         </section>
    </div>
</div>