Placer dans ce répertoire 'db_design' les fichiers de définition des tables et des vues. 

Il est préférable de placer chaque définition dans un fichier distinct.
Ces fichiers sont lus par la méthode getConfig() de ce module.

1. Structure définissant une table
----------------------------------
Le nom du fichier est de la forme table.{nom de la table}.php
Il contient :
return array(
    'name' => 'nom de la table', // par la suite dans ce document, ce nom est désigné `nom_court_de_la_table`
	'type' => 'table | system', // les `table` contiennent les données de l'utilisateur, les `system` contiennent des paramètres et objets du logiciel
    'drop' => false, // si true, un DROP TABLE IF EXISTS sera fait avant la création 
    'edit_entity' => true, // si false, on ne touche pas à la structure dans Create::createOrAlterEntity() - true par défaut
    'add_data' => true, // si false, on ne fait rien dans Create::addData() - true par défaut ; sans effet sur une vue
    'structure'=> array(
        'fields' => array(
            //définition de champs : 
            'nom_colonne' => "type [NOT NULL | NULL] [DEFAULT default_value] [AUTO_INCREMENT] [COMMENT 'string']"
            type:
			    TINYINT[(length)] [UNSIGNED] [ZEROFILL]
			  | SMALLINT[(length)] [UNSIGNED] [ZEROFILL]
			  | MEDIUMINT[(length)] [UNSIGNED] [ZEROFILL]
			  | INT[(length)] [UNSIGNED] [ZEROFILL]
			  | INTEGER[(length)] [UNSIGNED] [ZEROFILL]
			  | BIGINT[(length)] [UNSIGNED] [ZEROFILL]
			  | REAL[(length,decimals)] [UNSIGNED] [ZEROFILL]
			  | DOUBLE[(length,decimals)] [UNSIGNED] [ZEROFILL]
			  | FLOAT[(length,decimals)] [UNSIGNED] [ZEROFILL]
			  | DECIMAL(length,decimals) [UNSIGNED] [ZEROFILL]
			  | NUMERIC(length,decimals) [UNSIGNED] [ZEROFILL]
			  | DATE
			  | TIME
			  | TIMESTAMP
			  | DATETIME
			  | CHAR(length) [BINARY | ASCII | UNICODE]
			  | VARCHAR(length) [BINARY]
			  | TINYBLOB
			  | BLOB
			  | MEDIUMBLOB
			  | LONGBLOB
			  | TINYTEXT
			  | TEXT
			  | MEDIUMTEXT
			  | LONGTEXT
			  | ENUM(value1,value2,value3,...)
			  | SET(value1,value2,value3,...)
			  | spatial_type
            // exemples :
            'tableId' => 'int(5) NOT NULL AUTO_INCREMENT',
            'champ1' => 'varchar(30) NOT NULL',
            'champ2' => 'varchar(30) NOT NULL',
            'champ3' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT "255"',
        ),
        'primary_key' => array('nom de colonne composant la clé primaire',),
        'keys => array(
            	'nom de l'index' => array(
            							'unique' => false, // true si UNIQUE
            							'fields' => array(
            											'champ1 [(length)] [ASC | DESC]',
            											// il peut y avoir plusieurs champs
            							),
            	 ),
            	 // il peut y avoir plusieurs index de noms différents
        ),
        'foreign key' => array(
        		array(
        			'key' => string | array (nom de la clé ou tableau des noms composant la clé)
        			'references' => array(
        				'table' => nom de la table,
        				'fields' => array(
        					'champ1',
            				// il peut y avoir plusieurs champs
        				),
        				'on' => array(
        					'update' => 'CASCADE' | 'SET NULL' | 'NO ACTION' | 'RESTRICT',
        					'delete' => 'CASCADE' | 'SET NULL' | 'NO ACTION' | 'RESTRICT'
        				)
        			)
        		),
        		... autres foreign key
        ),
        'engine' => 'MyISAM', // on peut utiliser les différents engines proposés par MySql
                                     MyISAM, MEMORY et MERGE sont trois moteurs non transactionnels rapides et compacts
                                     InnoDB et BDB gèrent des tables transactionnelles et sont plus surs en cas d'échec (COMMIT, ROLLBACK)
        'charset' => 'utf8', // ou autre chose si on veut
        'collate' => 'utf8_unicode_ci', // ou autre chose
    ),
    'triggers' => array(
    	'nom du trigger' => array(
    		'moment' => 'BEFORE | AFTER',
    		'evenement' => 'INSERT | UPDATE | DELETE',
    		'definition' => <<<EOT
commandes SQL définissant les actions à accomplir. 
	Pour faire référence dans la commande SQL à une table de cette base de données, on utilisera la sytaxe suivante :
		%table(nom_court_de_la_table)% pour une table de type 'table' 
		%system(nom_cour_de_la_table)% pour une table de type 'system'
EOT
    	),
    	...
    ),
    'data' => chemin absolu du fichier contenant les données. On pourra de la façon suivante : __DIR__ . 'chemin relatif par rapport à ce fichier',
    ),
);

Les clés 'type', 'name' et 'drop' sont obligatoires.
Les autres clés sont facultatives et les tabeaux peuvent être vides.

2. Structure d'un fichier contenant des données
-----------------------------------------------
Ce fichier est indiqué dans la clé ['data']['include'] de structure définissant une table.
Il contient :
return array(
	// enregistrement 1
	array(
		'champ1' => valeur du champ 1,
		'champ2' => valeur du cham 2,
		... on peut ne pas donner de valeur à un champ si une valeur par défaut a été précisée lors de sa définition ou s'il peut être NULL
	),
	... autres enregistrements
);

3. Structure définissant une vue
--------------------------------
Le nom du fichier est de la forme vue.{nom de la vue}.php
Il contient:
return array(
    'name' => 'nom de la table',
    'drop' => false, // si true, un DROP TABLE IF EXISTS sera fait avant la création 
	'type' => 'table' ou 'vue'
    'structure'=> array(
    	'fields' => array(
    					array(
    						'alias' => 'nom_alias',  // optionnel sauf si la clé 'expression' est utilisée
     						'field' => 'nom_column', // interdite si la clé 'expression' est utilisée, obligatoire sinon
    						'expression' => array(   // interdite si la clé 'field' est utilisée, obligaoire sinon
    						    'value' => 'chaine_représentant_l_expression', 
    						    'type' => type du résultat de l'expression
    						) 
    					),
    					...   						
    						// 'field' ou 'expression' sont des clés obligatoires mais s'excluant mutuellement
    						// si la clé 'field' est présente, la clés 'alias' est optionnelle
    						// si la clé 'expression' est présente, la clé 'alias' est obligatoire 
    	),
    	'from' => array(
    				'table' => 'nom_table', // obligatoire mais peut être une vue
    				'type' => 'table' ou 'vue', // optionnel, 'table' par défaut
    				'alias' => 'nom_alias', // optionnel
    	),
    	'join' => array(
    				array(
    					'table' => 'nom_table', // obligatoire mais peut être une vue
    					'type' => 'table' ou 'vue', // optionnel, 'table' par défaut
    					'alias' => 'nom_alias', // optionnel
    					'relation' => 'chaine_représentant_la_relation', // obligatoire
    					'fields' => array( array(/* voir la structure ci-dessus*/),), // optionnel, liste des colonnes de cette table ou vue, 
    																				  // toutes par défaut
    																				  // chaque field est un array('alias'=>..., 'field'=>...)
    					'jointure' => 'type_de_join', // optionnel, Zend\Db\Sql\Select::JOIN_INNER par défaut
    				),
    				...
    	),
    	'where' => array(
    	),
    	'group' => array(
    	            array(
    	                'table' => 'nom_table ou nom_vue', 
    	                'field' =>'nom_column'
    	            ), 
    	           ...
    	)
    ),
);

Les clés 'name', 'drop', 'type' et 'structure' sont obligatoires.
Dans 'structure' les clés 'fields' et 'from' sont obligatoires ; les clés 'join' et 'where' sont optionnelles.
Les jointures sont définies dans Zend\Db\Sql\Select par les constantes : JOIN_INNER (par défaut), JOIN_OUTER, JOIN_LEFT et JOIN_RIGHT. 
(Ne pas oublier de placer "use Zend\Db\Sql\Select;" en tête de fichier de définition pour utiliser ces constantes.)

