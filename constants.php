<?php

include_once('conf/config.php');

/* Database Parameters */
define("SERVER",$db_host);
define("USERNAME",$db_username);
define("PASSWORD",$db_password);
define("DATABASE",$db_name);

/* Clustering Server Parameters*/
define("STATUT", $dcs_type);
define("CLUSTERING_SERVER",$clustering_server);
define("TOKEN",$dcs_token);
define("LANG",$dcs_lang);
