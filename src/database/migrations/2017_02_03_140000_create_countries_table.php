<?php

use WPBannerize\WPBones\Database\Migrations\Migration;

class Countries extends Migration
{

  public function up()
  {
    $this->create( "CREATE TABLE {$this->tablename} (
      id bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID Zone-Country',
      zone varchar(255) NOT NULL DEFAULT '' COMMENT 'Zone',
      country varchar(255) NOT NULL DEFAULT '' COMMENT 'Country name',
      isocode char(2) DEFAULT '' COMMENT 'ISO CODE',
      currency varchar(255) NOT NULL DEFAULT '' COMMENT 'Currency',
      symbol varchar(10) NOT NULL DEFAULT '' COMMENT 'Currency symbol',
      symbol_html varchar(10) NOT NULL DEFAULT '' COMMENT 'HTML currency symbol',
      code char(3) NOT NULL DEFAULT '' COMMENT 'Code',
      tax decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT 'Tax',
      continent varchar(20) NOT NULL DEFAULT '' COMMENT 'Continent',
      status enum('publish','trash') NOT NULL DEFAULT 'publish' COMMENT 'Status of record',
      PRIMARY KEY (id),
      KEY status (status)
    ) {$this->charsetCollate};" );
  }

}