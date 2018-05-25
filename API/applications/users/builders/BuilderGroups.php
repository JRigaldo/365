<?php
return [
    
    /**
     * Fields format used by the Orm
     */
    'groups' => [
        'IdGroup'           => [ 'type' => 'INT', 'autoincrement' => true, 'primary' => true, 'dependencies' => ['fonction_group'=>'IdGroup', 'beneficiaire'=>'groups']  ],
        'NameGroup'         => [ 'type' => 'STR' ],
        'IdMenuLanding'     => [ 'type' => 'INT', 'default' => 0 ],
        
    ],
    'group_rights' => [
        'IdGroup'       => [ 'type' => 'INT', 'mandatory' => true ],
        'IdMenu'        => [ 'type' => 'INT', 'mandatory' => true ],
        'Rights'        => [ 'type' => 'STR', 'mandatory' => true  ],
    ],

    
    /**
     * Jointer between tables by the foreign keys. Used by the Orm
     */
    'relations' => [
        'grouprights' => [
            'adminmenus'=>['grouprights'=>'IdMenu', 'adminmenus'=>'IdMenu']
        ],
        'groups' => [
            'adminmenus' => ['groups'=>'IdMenuLanding', 'adminmenus'=>'IdMenu']
        ]
    ]
    
];