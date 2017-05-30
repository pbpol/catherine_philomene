<?php
 
class Meta extends MetaCore
{

	public static function getHomeMetas($id_lang, $page_name)
    {
        $metas = Meta::getMetaByPage($page_name, $id_lang);
        $ret['meta_title'] = (isset($metas['title']) && $metas['title']) ? $metas['title'] : Configuration::get('PS_SHOP_NAME');
        $ret['meta_description'] = (isset($metas['description']) && $metas['description']) ? $metas['description'] : '';
        $ret['meta_keywords'] = (isset($metas['keywords']) && $metas['keywords']) ? $metas['keywords'] :  '';
        return $ret;
    }
	
	public static function getCategoryMetas($id_category, $id_lang, $page_name, $title = '')
    {
        if (!empty($title)) {
            $title = ' - '.$title;
        }
        $page_number = (int)Tools::getValue('p');
        $sql = 'SELECT `name`, `meta_title`, `meta_description`, `meta_keywords`, `description`
				FROM `'._DB_PREFIX_.'category_lang` cl
				WHERE cl.`id_lang` = '.(int)$id_lang.'
					AND cl.`id_category` = '.(int)$id_category.Shop::addSqlRestrictionOnLang('cl');

        $cache_id = 'Meta::getCategoryMetas'.(int)$id_category.'-'.(int)$id_lang;
        if (!Cache::isStored($cache_id)) {
            if ($row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql)) {
                if (empty($row['meta_description'])) {
                    $row['meta_description'] = strip_tags($row['description']);
                }

                // Paginate title
                if (!empty($row['meta_title'])) {
                    $row['meta_title'] = $title.$row['meta_title'].(!empty($page_number) ? ' ('.$page_number.')' : '');
                } else {
                    $row['meta_title'] = $row['name'].(!empty($page_number) ? ' ('.$page_number.')' : '');
                }

                if (!empty($title)) {
                    $row['meta_title'] = $title.(!empty($page_number) ? ' ('.$page_number.')' : '');
                }

                $result = Meta::completeMetaTags($row, $row['name']);
            } else {
                $result = Meta::getHomeMetas($id_lang, $page_name);
            }
            Cache::store($cache_id, $result);
            return $result;
        }
        return Cache::retrieve($cache_id);
    }
	
	public static function getSupplierMetas($id_supplier, $id_lang, $page_name)
    {
        $sql = 'SELECT `name`, `meta_title`, `meta_description`, `meta_keywords`
				FROM `'._DB_PREFIX_.'supplier_lang` sl
				LEFT JOIN `'._DB_PREFIX_.'supplier` s ON (sl.`id_supplier` = s.`id_supplier`)
				WHERE sl.id_lang = '.(int)$id_lang.'
					AND sl.id_supplier = '.(int)$id_supplier;
        if ($row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql)) {
            if (!empty($row['meta_description'])) {
                $row['meta_description'] = strip_tags($row['meta_description']);
            }
            if (!empty($row['meta_title'])) {
                $row['meta_title'] = $row['meta_title'];
            }
            return Meta::completeMetaTags($row, $row['name']);
        }

        return Meta::getHomeMetas($id_lang, $page_name);
    }
	
	public static function getCmsMetas($id_cms, $id_lang, $page_name)
    {
        $sql = 'SELECT `meta_title`, `meta_description`, `meta_keywords`
				FROM `'._DB_PREFIX_.'cms_lang`
				WHERE id_lang = '.(int)$id_lang.'
					AND id_cms = '.(int)$id_cms.
                    ((int)Context::getContext()->shop->id ?
                        ' AND id_shop = '.(int)Context::getContext()->shop->id : '');

        if ($row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql)) {
            $row['meta_title'] = $row['meta_title'];
            return Meta::completeMetaTags($row, $row['meta_title']);
        }

        return Meta::getHomeMetas($id_lang, $page_name);
    }
	
	  public static function getCmsCategoryMetas($id_cms_category, $id_lang, $page_name)
    {
        $sql = 'SELECT `meta_title`, `meta_description`, `meta_keywords`
				FROM `'._DB_PREFIX_.'cms_category_lang`
				WHERE id_lang = '.(int)$id_lang.'
					AND id_cms_category = '.(int)$id_cms_category.
                    ((int)Context::getContext()->shop->id ?
                        ' AND id_shop = '.(int)Context::getContext()->shop->id : '');
        if ($row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql)) {
            $row['meta_title'] = $row['meta_title'];
            return Meta::completeMetaTags($row, $row['meta_title']);
        }

        return Meta::getHomeMetas($id_lang, $page_name);
    }
	
	public static function completeMetaTags($meta_tags, $default_value, Context $context = null)
    {
        if (!$context) {
            $context = Context::getContext();
        }

        if (empty($meta_tags['meta_title'])) {
            $meta_tags['meta_title'] = $default_value;
        }
        if (empty($meta_tags['meta_description'])) {
            $meta_tags['meta_description'] = Configuration::get('PS_META_DESCRIPTION', $context->language->id) ? Configuration::get('PS_META_DESCRIPTION', $context->language->id) : '';
        }
        if (empty($meta_tags['meta_keywords'])) {
            $meta_tags['meta_keywords'] = Configuration::get('PS_META_KEYWORDS', $context->language->id) ? Configuration::get('PS_META_KEYWORDS', $context->language->id) : '';
        }
        return $meta_tags;
    }
	
}