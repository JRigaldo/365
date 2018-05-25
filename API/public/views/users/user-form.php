<?php self::_render( 'components/page-header', [ 
                            'title'             =>'Utilisateurs', 
                            'backbtn-display'   =>true, 
                            'backbtn-url'       =>'/users/users', 
                            'backbtn-label'     =>'Retour à la liste des utilisateurs'
                        ] ); ?>

<div class="row">
    
    <form action="<?php echo $datas->formDisplay['formaction']; ?>" method="post" class="form-horizontal form-label-left" enctype="multipart/form-data" >

    <div class="col-md-12 col-sm-12 col-xs-12">
        
        <section>
                  
            <?php self::_render( 'components/section-toolsheader', [ 
                                'title'           =>'Informations',
                                'subtitle'        =>' - Modifier', 
                                'response'        =>$datas->response
                            ] ); ?>

            <div class="clearfix">
                <br />

                <div class="col-md-6 col-xs-12">
                    <section>
                        <header class="tools-header">
                            <h2>Infos personnelles <small></small></h2>
                        </header>
                        <div class="clearfix">

                                <?php self::_render( 'components/form-field', [
                                            'title'=>'Nom', 
                                            'name'=>'LastnameUser', 
                                            'values'=>$datas->form, 
                                            'type'=>'input-text',  
                                            'required'=>true
                                    ] ); ?>

                                <?php self::_render( 'components/form-field', [
                                            'title'=>'Prenom', 
                                            'name'=>'FirstnameUser', 
                                            'values'=>$datas->form, 
                                            'type'=>'input-text', 
                                            'required'=>true
                                    ] ); ?>

                                <hr />
                            
                            
                                <?php self::_render( 'components/form-field', [
                                            'title'=>'Groupes', 
                                            'name'=>'IdGroup', 
                                            'values'=>$datas->form, 
                                            'type'=>'input-radio-list', 
                                            'options'=>$datas->groups,
                                            'option-value'=>'value', 
                                            'option-label'=>'label'
                                    ] ); ?>

                        </div>
                    </section>
                </div>

                <div class="col-md-6 col-xs-12">
                    <section>
                        <header class="tools-header">
                            <h2>Coordonnées <small></small></h2>
                        </header>
                        <div class="clearfix">
                            <?php self::_render( 'components/form-field', [
                                        'title'=>'Téléphone professionnel', 
                                        'name'=>'PhoneUser', 
                                        'values'=>$datas->form, 
                                        'type'=>'input-text',  
                                        'add-end'=>'<i class="mdi mdi-phone"></i>'
                                ] ); ?>

                            <?php self::_render( 'components/form-field', [
                                        'title'=>'E-mail', 
                                        'name'=>'EmailUser', 
                                        'values'=>$datas->form, 
                                        'type'=>'input-text', 
                                        'add-end'=>'<i class="mdi mdi-mail-ru"></i>', 
                                        'required'=>true
                                ] ); ?>
                            
                            <hr>
                            
                            <?php self::_render( 'components/form-field', [
                                        'title'=>'Adresse', 
                                        'name'=>'AddressUser', 
                                        'values'=>$datas->form, 
                                        'type'=>'input-text', 
                                ] ); ?>

                            <?php self::_render( 'components/form-field', [
                                        'title'=>'NPA', 
                                        'name'=>'ZipCodeUser', 
                                        'values'=>$datas->form, 
                                        'type'=>'input-text', 
                                ] ); ?>

                            <?php self::_render( 'components/form-field', [
                                        'title'=>'Ville', 
                                        'name'=>'CityUser', 
                                        'values'=>$datas->form, 
                                        'type'=>'input-text', 
                                ] ); ?>

                            <?php self::_render( 'components/form-field', [
                                        'title'=>'Pays', 
                                        'name'=>'CountryUser', 
                                        'values'=>$datas->form,
                                        'type'=>'select',
                                        'options'=>$datas->countries,
                                        'option-value'=>'value', 
                                        'option-label'=>'option',
                                        'option-selected'=>( !empty( $datas->form->PaysBeneficiaire )  ? $datas->form->PaysBeneficiaire : 174 )
                                ] ); ?>

                        </div>
                    </section>
                </div>

            </div>
            
         </section>
        
    </div>
         
         
    <div class="col-md-12 col-sm-12 col-xs-12">

        <section>
            <div class="form-group">
                <div class="col-md-9 col-sm-9 col-xs-12 col-md-offset-3">
                    <button type="submit" class="btn btn-success">Envoyer</button>
                </div>
            </div>
        </section>
    </div>
    
    </form>

</div>