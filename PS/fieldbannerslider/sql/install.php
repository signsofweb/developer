<?php

    // Init
    $sql = array();

    // Create Table in Database
    $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'fieldbannerslider` (
                        `id_fieldbannerslider` int(10) NOT NULL AUTO_INCREMENT,
                        `porder` int NOT NULL,
                        `active` int NOT NULL,
                        `images` varchar(250) NOT NULL,
                        PRIMARY KEY (`id_fieldbannerslider`)
                    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
					
	$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'fieldbannerslider_lang` (
                        `id_fieldbannerslider` int(11) unsigned NOT NULL,
                        `id_lang` int(11) unsigned NOT NULL,
                        `title` varchar(250) NOT NULL,
                        `link` varchar(250) NOT NULL DEFAULT "#",
                        `description` longtext NOT NULL,
                        `image` longtext NOT NULL,
                        PRIMARY KEY (`id_fieldbannerslider`,`id_lang`)
                    ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';									

    $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'fieldbannerslider_shop` (
                        `id_fieldbannerslider` int(11) unsigned NOT NULL,
                        `id_shop` int(11) unsigned NOT NULL,
                        PRIMARY KEY (`id_fieldbannerslider`,`id_shop`)
                    ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
					
    
    $doc = new DOMDocument();
    $file = _PS_MODULE_DIR_ . DS . 'fieldbannerslider' . DS . 'sql' . DS . 'bannerslider.xml';
    $doc->load($file);
    $blocks = $doc->getElementsByTagName("bannerslider");
    foreach ($blocks as $block) {
        $ids = $block->getElementsByTagName("id_fieldbannerslider");
        $id = $ids->item(0)->nodeValue;
        $actives = $block->getElementsByTagName("active");
        $active = $actives->item(0)->nodeValue;
        $porders = $block->getElementsByTagName("porder");
        $porder = $porders->item(0)->nodeValue;
        $images = $block->getElementsByTagName("images");
        $image = $images->item(0)->nodeValue;
        $sql[] = "insert into `" . _DB_PREFIX_ . "fieldbannerslider` (`id_fieldbannerslider`,`porder`,`active`,`images`)
				  values('".$id."','".$porder."','".$active."','".$image."');";
    }
	
	
	$blocklangs = $doc->getElementsByTagName("bannerslider_lang");
    foreach ($blocklangs as $block) {
        $ids = $block->getElementsByTagName("id_fieldbannerslider");
        $id = $ids->item(0)->nodeValue;
        $titles = $block->getElementsByTagName("title");
        $title = $titles->item(0)->nodeValue;
        $links = $block->getElementsByTagName("link");
        $link = $links->item(0)->nodeValue;
        $descriptions = $block->getElementsByTagName("description");
        $description = $descriptions->item(0)->nodeValue;
        $id_langs = $block->getElementsByTagName('id_lang');
        $id_lang = $id_langs->item(0)->nodeValue;
        $sql[] = "insert into `" . _DB_PREFIX_ . "fieldbannerslider_lang` (`id_fieldbannerslider`,`id_lang`, `title`, `link`, `description`)
           values('".$id."','".$id_lang."','".$title."','".$link."','".$description."');";
    }

    $blockshops = $doc->getElementsByTagName("bannerslider_shop");
    foreach ($blockshops as $blockshop) {
        $ids = $blockshop->getElementsByTagName("id_fieldbannerslider");
        $id = $ids->item(0)->nodeValue;
        $id_shops = $blockshop->getElementsByTagName("id_shop");
        $id_shop = $id_shops->item(0)->nodeValue;
        //echo $id.'-'.$id_shop;
        $sql[] = "insert into `" . _DB_PREFIX_ . "fieldbannerslider_shop`(`id_fieldbannerslider`, `id_shop`)
                VALUES('" . $id . "','" . $id_shop . "')";
    }