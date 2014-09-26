<?php

/**
 * moziloCMS Plugin: SpecialMenu
 *
 * Offers additional menu functions
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_MoziloPlugins
 * @author   DEVMOUNT <mail@devmount.de>
 * @license  GPL v3+
 * @version  GIT: v0.1.2014-09-26
 * @link     https://github.com/devmount/SpecialMenu
 * @link     http://devmount.de/Develop/moziloCMS/Plugins/SpecialMenu.html
 * @see      Verse
 *           – The Bible
 *
 * Plugin created by DEVMOUNT
 * www.devmount.de
 *
 */

// only allow moziloCMS environment
if (!defined('IS_CMS')) {
    die();
}

/**
 * SpecialMenu Class
 *
 * @category PHP
 * @package  PHP_MoziloPlugins
 * @author   DEVMOUNT <mail@devmount.de>
 * @license  GPL v3+
 * @link     https://github.com/devmount/SpecialMenu
 */
class SpecialMenu extends Plugin
{
    // language
    private $_admin_lang;
    private $_cms_lang;

    // plugin information
    const PLUGIN_AUTHOR  = 'DEVMOUNT';
    const PLUGIN_TITLE   = 'SpecialMenu';
    const PLUGIN_VERSION = 'v0.1.2014-09-26';
    const MOZILO_VERSION = '2.0';
    const PLUGIN_DOCU
        = 'http://devmount.de/Develop/moziloCMS/Plugins/SpecialMenu.html';

    private $_plugin_tags = array(
        'tag1' => '{SpecialMenu|main}',
        'tag2' => '{SpecialMenu|if|<category>|<content>}',
        'tag3' => '{SpecialMenu|ifnot|<category>|<content>}',
        'tag4' => '{SpecialMenu|pages|<category>}',
    );

    const LOGO_URL = 'http://media.devmount.de/logo_pluginconf.png';

    /**
     * set configuration elements, their default values and their configuration
     * parameters
     *
     * @var array $_confdefault
     *      text     => default, type, maxlength, size, regex
     *      textarea => default, type, cols, rows, regex
     *      password => default, type, maxlength, size, regex, saveasmd5
     *      check    => default, type
     *      radio    => default, type, descriptions
     *      select   => default, type, descriptions, multiselect
     */
    private $_confdefault = array(
        'hidefirstcat' => array(
            'true',
            'check',
        ),
        'showcatlikepage' => array(
            'false',
            'check',
        ),
        'showhidden' => array(
            'false',
            'check',
        ),
        'showdraft' => array(
            'false',
            'check',
        ),
    );

    /**
     * creates plugin content
     *
     * @param string $value Parameter divided by '|'
     *
     * @return string HTML output
     */
    function getContent($value)
    {
        global $CMS_CONF;
        global $syntax;
        global $CatPage;

        // initialize cms lang
        $this->_cms_lang = new Language(
            $this->PLUGIN_SELF_DIR
            . 'lang/cms_language_'
            . $CMS_CONF->get('cmslanguage')
            . '.txt'
        );

        // get params
        list($param_type, $param_category, $param_content) = explode('|', $value);
        $param_type = trim($param_type);
        $param_category = trim($param_category);
        $param_content = trim($param_content);

        // get conf and set default
        $conf = array();
        foreach ($this->_confdefault as $elem => $default) {
            $conf[$elem] = ($this->settings->get($elem) == '')
                ? $default[0]
                : $this->settings->get($elem);
        }

        // get file extensions
        $extensions = array(EXT_PAGE);
        if ($conf['showhidden'] == 'true') {
            $extensions[] = EXT_HIDDEN;
        }
        if ($conf['showdraft'] == 'true') {
            $extensions[] = EXT_DRAFT;
        }

        // initialize return content, begin plugin content
        $content = '';

        switch ($param_type) {
            case 'main':
                $cats = $CatPage->get_CatArray();
                if ($conf['hidefirstcat'] == 'true') {
                    array_shift($cats);
                }
                foreach ($cats as $cat) {
                    $content .=
                        '[kategorie='. urldecode($cat)
                        . '|@='. $cat . '=@]';
                }
                break;

            case 'if':
                if ($param_category == CAT_REQUEST) {
                    $content .= $param_content;
                }
                break;

            case 'ifnot':
                if ($param_category != CAT_REQUEST) {
                    $content .= $param_content;
                }
                break;

            case 'pages':
                // get pages of given category
                $pagearray = $CatPage->get_PageArray(
                    $param_category,
                    $extensions
                );
                // real cat name without '/'
                if (strpos($cat, '%2F') !== false) {
                    $cat = substr($param_category, $this->strrpos($param_category, '%2F')+3);
                }
                // build page list
                foreach ($pagearray as $page) {
                    // handle page with same name as category
                    if ($conf['showcatlikepage'] == 'true' or $page != $cat) {
                        // build page link with mozilo syntax [seite|...]
                        $content .=
                            '[seite='. urldecode($page)
                            . '|@='. $param_category . ':'. $page . '=@]';
                    }
                }
                break;

            default:
                # code...
                break;
        }

        return $content;
    }

    /**
     * sets backend configuration elements and template
     *
     * @return Array configuration
     */
    function getConfig()
    {
        $config = array();

        // read configuration values
        foreach ($this->_confdefault as $key => $value) {
            // handle each form type
            switch ($value[1]) {
            case 'text':
                $config[$key] = $this->confText(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $value[2],
                    $value[3],
                    $value[4],
                    $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_error'
                    )
                );
                break;

            case 'textarea':
                $config[$key] = $this->confTextarea(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $value[2],
                    $value[3],
                    $value[4],
                    $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_error'
                    )
                );
                break;

            case 'password':
                $config[$key] = $this->confPassword(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $value[2],
                    $value[3],
                    $value[4],
                    $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_error'
                    ),
                    $value[5]
                );
                break;

            case 'check':
                $config[$key] = $this->confCheck(
                    $this->_admin_lang->getLanguageValue('config_' . $key)
                );
                break;

            case 'radio':
                $descriptions = array();
                foreach ($value[2] as $label) {
                    $descriptions[$label] = $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_' . $label
                    );
                }
                $config[$key] = $this->confRadio(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $descriptions
                );
                break;

            case 'select':
                $descriptions = array();
                foreach ($value[2] as $label) {
                    $descriptions[$label] = $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_' . $label
                    );
                }
                $config[$key] = $this->confSelect(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $descriptions,
                    $value[3]
                );
                break;

            default:
                break;
            }
        }

        // read admin.css
        $admin_css = '';
        $lines = file('../plugins/' . self::PLUGIN_TITLE. '/admin.css');
        foreach ($lines as $line_num => $line) {
            $admin_css .= trim($line);
        }

        // add template CSS
        $template = '<style>' . $admin_css . '</style>';

        // build Template
        $template .= '
            <div class="specialmenu-admin-header">
            <span>'
                . $this->_admin_lang->getLanguageValue(
                    'admin_header',
                    self::PLUGIN_TITLE
                )
            . '</span>
            <a href="' . self::PLUGIN_DOCU . '" target="_blank">
            <img style="float:right;" src="' . self::LOGO_URL . '" />
            </a>
            </div>
        </li>
        <li class="mo-in-ul-li ui-widget-content specialmenu-admin-li">
            <div class="specialmenu-admin-subheader">'
            . $this->_admin_lang->getLanguageValue('admin_display')
            . '</div>
            <div class="specialmenu-single-conf">
                {hidefirstcat_checkbox}
                {hidefirstcat_description}
                <span class="specialmenu-admin-default">
                    [' . $this->_confdefault['hidefirstcat'][0] .']
                </span>
            </div>
            <div class="specialmenu-single-conf">
                {showcatlikepage_checkbox}
                {showcatlikepage_description}
                <span class="specialmenu-admin-default">
                    [' . $this->_confdefault['showcatlikepage'][0] .']
                </span>
            </div>
            <div class="specialmenu-single-conf">
                {showhidden_checkbox}
                {showhidden_description}
                <span class="specialmenu-admin-default">
                    [' . $this->_confdefault['showhidden'][0] .']
                </span>
            </div>
            <div class="specialmenu-single-conf">
                {showdraft_checkbox}
                {showdraft_description}
                <span class="specialmenu-admin-default">
                    [' . $this->_confdefault['showdraft'][0] .']
                </span>
        ';

        $config['--template~~'] = $template;

        return $config;
    }

    /**
     * sets default backend configuration elements, if no plugin.conf.php is
     * created yet
     *
     * @return Array configuration
     */
    function getDefaultSettings()
    {
        $config = array('active' => 'true');
        foreach ($this->_confdefault as $elem => $default) {
            $config[$elem] = $default[0];
        }
        return $config;
    }

    /**
     * sets backend plugin information
     *
     * @return Array information
     */
    function getInfo()
    {
        global $ADMIN_CONF;

        $this->_admin_lang = new Language(
            $this->PLUGIN_SELF_DIR
            . 'lang/admin_language_'
            . $ADMIN_CONF->get('language')
            . '.txt'
        );

        // build plugin tags
        $tags = array();
        foreach ($this->_plugin_tags as $key => $tag) {
            $tags[$tag] = $this->_admin_lang->getLanguageValue('tag_' . $key);
        }

        $info = array(
            '<b>' . self::PLUGIN_TITLE . '</b> ' . self::PLUGIN_VERSION,
            self::MOZILO_VERSION,
            $this->_admin_lang->getLanguageValue(
                'description',
                htmlspecialchars($this->_plugin_tags['tag1']),
                htmlspecialchars($this->_plugin_tags['tag2'])
            ),
            self::PLUGIN_AUTHOR,
            array(
                self::PLUGIN_DOCU,
                self::PLUGIN_TITLE . ' '
                . $this->_admin_lang->getLanguageValue('on_devmount')
            ),
            $tags
        );

        return $info;
    }

    /**
     * creates configuration for text fields
     *
     * @param string $description Label
     * @param string $maxlength   Maximum number of characters
     * @param string $size        Size
     * @param string $regex       Regular expression for allowed input
     * @param string $regex_error Wrong input error message
     *
     * @return Array  Configuration
     */
    protected function confText(
        $description,
        $maxlength = '',
        $size = '',
        $regex = '',
        $regex_error = ''
    ) {
        // required properties
        $conftext = array(
            'type' => 'text',
            'description' => $description,
        );
        // optional properties
        if ($maxlength != '') {
            $conftext['maxlength'] = $maxlength;
        }
        if ($size != '') {
            $conftext['size'] = $size;
        }
        if ($regex != '') {
            $conftext['regex'] = $regex;
        }
        if ($regex_error != '') {
            $conftext['regex_error'] = $regex_error;
        }
        return $conftext;
    }

    /**
     * creates configuration for textareas
     *
     * @param string $description Label
     * @param string $cols        Number of columns
     * @param string $rows        Number of rows
     * @param string $regex       Regular expression for allowed input
     * @param string $regex_error Wrong input error message
     *
     * @return Array  Configuration
     */
    protected function confTextarea(
        $description,
        $cols = '',
        $rows = '',
        $regex = '',
        $regex_error = ''
    ) {
        // required properties
        $conftext = array(
            'type' => 'textarea',
            'description' => $description,
        );
        // optional properties
        if ($cols != '') {
            $conftext['cols'] = $cols;
        }
        if ($rows != '') {
            $conftext['rows'] = $rows;
        }
        if ($regex != '') {
            $conftext['regex'] = $regex;
        }
        if ($regex_error != '') {
            $conftext['regex_error'] = $regex_error;
        }
        return $conftext;
    }

    /**
     * creates configuration for password fields
     *
     * @param string  $description Label
     * @param string  $maxlength   Maximum number of characters
     * @param string  $size        Size
     * @param string  $regex       Regular expression for allowed input
     * @param string  $regex_error Wrong input error message
     * @param boolean $saveasmd5   Safe password as md5 (recommended!)
     *
     * @return Array   Configuration
     */
    protected function confPassword(
        $description,
        $maxlength = '',
        $size = '',
        $regex = '',
        $regex_error = '',
        $saveasmd5 = true
    ) {
        // required properties
        $conftext = array(
            'type' => 'text',
            'description' => $description,
        );
        // optional properties
        if ($maxlength != '') {
            $conftext['maxlength'] = $maxlength;
        }
        if ($size != '') {
            $conftext['size'] = $size;
        }
        if ($regex != '') {
            $conftext['regex'] = $regex;
        }
        $conftext['saveasmd5'] = $saveasmd5;
        return $conftext;
    }

    /**
     * creates configuration for checkboxes
     *
     * @param string $description Label
     *
     * @return Array  Configuration
     */
    protected function confCheck($description)
    {
        // required properties
        return array(
            'type' => 'checkbox',
            'description' => $description,
        );
    }

    /**
     * creates configuration for radio buttons
     *
     * @param string $description  Label
     * @param string $descriptions Array Single item labels
     *
     * @return Array Configuration
     */
    protected function confRadio($description, $descriptions)
    {
        // required properties
        return array(
            'type' => 'select',
            'description' => $description,
            'descriptions' => $descriptions,
        );
    }

    /**
     * creates configuration for select fields
     *
     * @param string  $description  Label
     * @param string  $descriptions Array Single item labels
     * @param boolean $multiple     Enable multiple item selection
     *
     * @return Array   Configuration
     */
    protected function confSelect($description, $descriptions, $multiple = false)
    {
        // required properties
        return array(
            'type' => 'select',
            'description' => $description,
            'descriptions' => $descriptions,
            'multiple' => $multiple,
        );
    }

    /**
     * throws styled message
     *
     * @param string $type Type of message ('ERROR', 'SUCCESS')
     * @param string $text Content of message
     *
     * @return string HTML content
     */
    protected function throwMessage($text, $type)
    {
        return '<div class="'
                . strtolower(self::PLUGIN_TITLE . '-' . $type)
            . '">'
            . '<div>'
                . $this->_cms_lang->getLanguageValue(strtolower($type))
            . '</div>'
            . '<span>' . $text. '</span>'
            . '</div>';
    }

    /**
     * finds last occurence of substring
     *
     * @param  string $haystack
     * @param  string $needle
     * @param  int    $offset
     *
     * @return int position of last occurence
     */
    protected function strrpos($haystack,$needle,$offset=NULL) {
        return strlen($haystack)
            - strpos( strrev($haystack) , strrev($needle) , $offset)
            - strlen($needle);
    }
}

?>