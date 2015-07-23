<?php
class PluginCustomStyle extends CommonDBTM {
   static $rightname = 'config';

   static function getTypeName($nb=0) {
      return __('style', 'custom');
   }

   function showForm($ID, $options=array()) {
      global $CFG_GLPI;

      $options['colspan'] = 4;
      $this->initForm($ID, $options);
      $this->showFormHeader($this->fields);

      echo "<tr><th colspan='4'>".__('Customise GLPI style', 'custom')."</th></tr>";

      echo "<tr>";
      echo "<td>##BODY##</td>";
      echo "<td>";
      Html::showColorField('body', array('value' => $this->fields['body']));
      echo "</td>";

      echo "<tr><th colspan='4'>Button</th></tr>";

      echo "<tr>";
      echo "<td>##BUTTON_BG_COLOR##</td>";
      echo "<td>";
      Html::showColorField('button_bg_color', array('value' => $this->fields['button_bg_color']));
      echo "</td>";

      echo "<td>##BUTTON_BG_COLOR_HOVER##</td>";
      echo "<td>";
      Html::showColorField('button_bg_color_hover', array('value' => $this->fields['button_bg_color_hover']));
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td>##BUTTON_BORDER##</td>";
      echo "<td>";
      Html::showColorField('button_border', array('value' => $this->fields['button_border']));
      echo "</td>";

      echo "<td>##BUTTON_BORDER_HOVER##</td>";
      echo "<td>";
      Html::showColorField('button_border_hover', array('value' => $this->fields['button_border_hover']));
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td>##BUTTON_COLOR##</td>";
      echo "<td>";
      Html::showColorField('button_color', array('value' => $this->fields['button_color']));
      echo "</td>";

      echo "<td>##BUTTON_COLOR_HOVER##</td>";
      echo "<td>";
      Html::showColorField('button_color_hover', array('value' => $this->fields['button_color_hover']));
      echo "</td>";
      echo "</tr>";


      echo "<td>##TEXT_COLOR##</td>";
      echo "<td>";
      Html::showColorField('text_color', array('value' => $this->fields['text_color']));
      echo "</td>";
      echo "</tr>";

      echo "<tr><th colspan='4'>Links</th></tr>";

      echo "<tr>";
      echo "<td>##LINK_COLOR##</td>";
      echo "<td>";
      Html::showColorField('link_color', array('value' => $this->fields['link_color']));
      echo "</td>";

      echo "<td>##HOVER_LINK_COLOR##</td>";
      echo "<td>";
      Html::showColorField('link_hover_color', array('value' => $this->fields['link_hover_color']));
      echo "</td>";
      echo "</tr>";

      echo "<tr><th colspan='4'>Menu</th></tr>";

      echo "<tr>";
      echo "<td>##MENU_BORDER##</td>";
      echo "<td>";
      Html::showColorField('menu_border', array('value' => $this->fields['menu_border']));
      echo "</td>";
      echo "<td>##MENU_ITEM_BG##</td>";
      echo "<td>";
      Html::showColorField('menu_item_bg', array('value' => $this->fields['menu_item_bg']));
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td>##MENU_ITEM_BORDER##</td>";
      echo "<td>";
      Html::showColorField('menu_item_border', array('value' => $this->fields['menu_item_border']));
      echo "</td>";

      echo "<td>##MENU_ITEM_BG_HOVER##</td>";
      echo "<td>";
      Html::showColorField('menu_item_bg_hover', array('value' => $this->fields['menu_item_bg_hover']));
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td>##MENU_LINK##</td>";
      echo "<td>";
      Html::showColorField('menu_link', array('value' => $this->fields['menu_link']));
      echo "</td>";

      echo "<td>##MENU_ITEM_LINK##</td>";
      echo "<td>";
      Html::showColorField('menu_item_link', array('value' => $this->fields['menu_item_link']));
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td>##SSMENU1_LINK##</td>";
      echo "<td>";
      Html::showColorField('ssmenu1_link', array('value' => $this->fields['ssmenu1_link']));
      echo "</td>";

      echo "<td>##SSMENU2_LINK##</td>";
      echo "<td>";
      Html::showColorField('ssmenu2_link', array('value' => $this->fields['ssmenu2_link']));
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td>##LINK_TOPRIGHT##</td>";
      echo "<td>";
      Html::showColorField('link_topright', array('value' => $this->fields['link_topright']));
      echo "</td>";
      echo "</tr>";

      echo "<tr><th colspan='4'>Tables</th></tr>";

      echo "<tr>";
      echo "<td>##TH##</td>";
      echo "<td>";
      Html::showColorField('th', array('value' => $this->fields['th']));
      echo "</td>";

      echo "<td>##TH_TEXT_COLOR##</td>";
      echo "<td>";
      Html::showColorField('th_text_color', array('value' => $this->fields['th_text_color']));
      echo "</td>";

      echo "</tr>";

      echo "<tr>";
      echo "<td>##TABLE_BG_COLOR##</td>";
      echo "<td>";
      Html::showColorField('table_bg_color', array('value' => $this->fields['table_bg_color']));
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td>##TAB_BG_1##</td>";
      echo "<td>";
      Html::showColorField('tab_bg_1', array('value' => $this->fields['tab_bg_1']));
      echo "</td>";


      echo "<td>##TAB_BG_2##</td>";
      echo "<td>";
      Html::showColorField('tab_bg_2', array('value' => $this->fields['tab_bg_2']));
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td>##TAB_BG_1_2##</td>";
      echo "<td>";
      Html::showColorField('tab_bg_1_2', array('value' => $this->fields['tab_bg_1_2']));
      echo "</td>";


      echo "<td>##TAB_BG_2_2##</td>";
      echo "<td>";
      Html::showColorField('tab_bg_2_2', array('value' => $this->fields['tab_bg_2_2']));
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td>##TAB_BG_3##</td>";
      echo "<td>";
      Html::showColorField('tab_bg_3', array('value' => $this->fields['tab_bg_3']));
      echo "</td>";

      echo "<td>##TAB_BG_4##</td>";
      echo "<td>";
      Html::showColorField('tab_bg_4', array('value' => $this->fields['tab_bg_4']));
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td>##TAB_BG_5##</td>";
      echo "<td>";
      Html::showColorField('tab_bg_5', array('value' => $this->fields['tab_bg_5']));
      echo "</td>";
      echo "</tr>";

      echo "<tr><th colspan='4'>Cadres</th></tr>";

      echo "<tr>";
      echo "<td>##CADRE_CENTRAL_BG1##</td>";
      echo "<td>";
      Html::showColorField('cadre_central_bg1', array('value' => $this->fields['cadre_central_bg1']));
      echo "</td>";

      echo "<td>##CADRE_CENTRAL_BG1##</td>";
      echo "<td>";
      Html::showColorField('cadre_central_bg2', array('value' => $this->fields['cadre_central_bg2']));
      echo "</td>";
      echo "</tr>";

      echo "<tr><th colspan='4'>Onglets</th></tr>";

      echo "<tr>";
      echo "<td>##TABS_BG1##</td>";
      echo "<td>";
      Html::showColorField('tabs_bg1', array('value' => $this->fields['tabs_bg1']));
      echo "</td>";

      echo "<td>##TABS_BG2##</td>";
      echo "<td>";
      Html::showColorField('tabs_bg2', array('value' => $this->fields['tabs_bg2']));
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td>##TABS_BG3##</td>";
      echo "<td>";
      Html::showColorField('tabs_bg3', array('value' => $this->fields['tabs_bg3']));
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td>##TABS_BORDER##</td>";
      echo "<td>";
      Html::showColorField('tabs_border', array('value' => $this->fields['tabs_border']));
      echo "</td>";

      echo "<td>##TABS_TITLE_COLOR##</td>";
      echo "<td>";
      Html::showColorField('tabs_title_color', array('value' => $this->fields['tabs_title_color']));
      echo "</td>";
      echo "</tr>";


      echo "<tr><th colspan='4'>Header</th></tr>";

      echo "<tr>";
      echo "<td>##HEADER_BG1##</td>";
      echo "<td>";
      Html::showColorField('header_bg1', array('value' => $this->fields['header_bg1']));
      echo "</td>";

      echo "<td>##HEADER_BG2##</td>";
      echo "<td>";
      Html::showColorField('header_bg2', array('value' => $this->fields['header_bg2']));
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td>##HEADER_BG3##</td>";
      echo "<td>";
      Html::showColorField('header_bg3', array('value' => $this->fields['header_bg3']));
      echo "</td>";

      echo "<td>##HEADER_BG4##</td>";
      echo "<td>";
      Html::showColorField('header_bg4', array('value' => $this->fields['header_bg4']));
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td>##HEADER_BG5##</td>";
      echo "<td>";
      Html::showColorField('header_bg5', array('value' => $this->fields['header_bg5']));
      echo "</td>";

      echo "<td>##HEADER_BG6##</td>";
      echo "<td>";
      Html::showColorField('header_bg6', array('value' => $this->fields['header_bg6']));
      echo "</td>";
      echo "</tr>";

      echo "<tr><th colspan='4'>Ombres</th></tr>";

      echo "<tr>";
      echo "<td>##HEADER_SHADOW_COLOR##</td>";
      echo "<td>";
      Html::showColorField('header_shadow_color', array('value' => $this->fields['header_shadow_color']));
      echo "</td>";

      echo "<td>##HEADER_SHADOW_SIZE##</td>";
      echo "<td>";
      ##size input
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td>##PAGE_SHADOW_COLOR##</td>";
      echo "<td>";
      Html::showColorField('page_shadow_color', array('value' => $this->fields['page_shadow_color']));
      echo "</td>";

      echo "<td>##PAGE_SHADOW_SIZE##</td>";
      echo "<td>";
      ##size input
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td>##FOOTER_SHADOW_COLOR##</td>";
      echo "<td>";
      Html::showColorField('footer_shadow_color', array('value' => $this->fields['footer_shadow_color']));
      echo "</td>";

      echo "<td>##FOOTER_SHADOW_SIZE##</td>";
      echo "<td>";
      ##size input
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td>##FOOTER_BG1##</td>";
      echo "<td>";
      Html::showColorField('footer_bg1', array('value' => $this->fields['footer_bg1']));
      echo "</td>";

      echo "<td>##FOOTER_BG2##</td>";
      echo "<td>";
      Html::showColorField('footer_bg2', array('value' => $this->fields['footer_bg2']));
      echo "</td>";
      echo "</tr>";

      $this->showFormButtons($options);
   }


   function post_updateItem($history=1) {
      global $CFG_GLPI;

      //generate header gradient img for internet explorer
      $gradient = new PluginCustomGradientgd;
      $image = $gradient->generate_gradient(1, 60, array(
         0   => $this->fields['header_bg2'],
         31  => $this->fields['header_bg3'],
         32  => $this->fields['header_bg4'],
         66  => $this->fields['header_bg4'],
         67  => $this->fields['header_bg5'],
         100 => $this->fields['header_bg5']
      ), 'vertical');
      $gradient->save_image($image, GLPI_ROOT."/files/_plugins/custom/fn_nav.png", "png");

      //generate css
      $CSS = "
      body {
         background-color: {$this->fields['body']};
         color: {$this->fields['text_color']}
      }

      .submit {
         background: none;
         background-color: {$this->fields['button_bg_color']};
         color: {$this->fields['button_color']};
         border: 1px solid {$this->fields['button_border']};
         padding: 3px 15px;
      }

      .submit:hover {
         background:none;
         background-color: {$this->fields['button_bg_color_hover']};
         color: {$this->fields['button_color_hover']};
          border: 1px solid {$this->fields['button_border_hover']};
      }

      div#header div#c_logo {
         background: url('".$CFG_GLPI['root_doc']."/plugins/custom/pics/fd_logo.png') 0 0 repeat-x;
      }

      a, a:link {
         color: {$this->fields['link_color']};
      }

      a:hover {
        color: {$this->fields['link_hover_color']};
      }

      ul#menu a.itemP, ul#menu a.itemP1 {
         color: {$this->fields['menu_link']};
         border-right: 1px solid {$this->fields['menu_border']};
      }

      ul#menu ul.ssmenu {
         background-image:none;
         background-color:{$this->fields['menu_item_bg']};
         border: 1px solid {$this->fields['menu_item_border']};
      }

      ul#menu ul li {
         border-bottom: 1px solid {$this->fields['menu_item_border']};
      }

      ul#menu ul li a {
         color: {$this->fields['menu_item_link']};
      }

      ul#menu ul li a:hover {
         background-image:none;
         background-color:{$this->fields['menu_item_bg_hover']};
      }

      div#c_ssmenu1 {
         background-image:none;
      }

      div#c_ssmenu1 ul li a {
         color:{$this->fields['ssmenu1_link']};
      }

      div#c_ssmenu2 {
         background-image:none;
      }

      div#c_ssmenu2 ul li a {
         color:{$this->fields['ssmenu2_link']};
      }

      div#show_all_menu {
         border: 1px solid {$this->fields['menu_item_border']};
         background-image:none;
         background-color:{$this->fields['menu_item_bg']};
      }

      div#c_preference ,
      div#c_preference a {
         color: {$this->fields['link_topright']};
      }

      #debug-float a {
         color:red;
      }

      .tab_cadre_fixe, .tab_cadre_fixehov {
         background: {$this->fields['table_bg_color']};
      }

      .tab_cadre th, .tab_cadre_fixe th, .tab_cadre_fixehov th,
      .tab_cadrehov th, .tab_cadrehov_pointer th, .tab_cadre_report th {
         background-color:{$this->fields['th']};
         color:{$this->fields['th_text_color']};
      }

      .tab_bg_1 {
         background-color: {$this->fields['tab_bg_1']};
      }

      .tab_bg_1_2 {
         background-color: {$this->fields['tab_bg_1_2']};
      }

      .tab_bg_2 {
         background-color: {$this->fields['tab_bg_2']};
      }

      .tab_bg_2_2 {
         background-color: {$this->fields['tab_bg_2_2']};
      }

      .tab_bg_3 {
         background-color: {$this->fields['tab_bg_3']};
      }

      .tab_bg_4 {
         background-color: {$this->fields['tab_bg_4']};
      }

      .tab_bg_5 {
         background-color: {$this->fields['tab_bg_5']};
      }

      .tab_cadre_central {
         background:-webkit-linear-gradient(top,
            {$this->fields['cadre_central_bg1']}, {$this->fields['cadre_central_bg2']});
         background:-moz-linear-gradient(top,
            {$this->fields['cadre_central_bg1']}, {$this->fields['cadre_central_bg2']});
         background:-o-linear-gradient(top,
            {$this->fields['cadre_central_bg1']}, {$this->fields['cadre_central_bg2']});
         background:linear-gradient(top,
            {$this->fields['cadre_central_bg1']}, {$this->fields['cadre_central_bg2']});
      }

      div#header {
         -moz-box-shadow: 0px 7px 10px {$this->fields['header_shadow_color']};
         -webkit-box-shadow: 0px 7px 10px {$this->fields['header_shadow_color']};
         box-shadow: 0px 7px 10px {$this->fields['header_shadow_color']};
         background: #ffffff; /* Old browsers */
         background: -moz-linear-gradient(top,
            {$this->fields['header_bg1']} 0%,
            {$this->fields['header_bg2']} 2%, {$this->fields['header_bg3']} 20%,
            {$this->fields['header_bg4']} 21%, {$this->fields['header_bg4']} 40%,
            {$this->fields['header_bg5']} 40%, {$this->fields['header_bg5']} 63%,
            {$this->fields['header_bg6']} 65%, {$this->fields['header_bg6']} 99%
            ); /* FF3.6+ */
         background: -webkit-gradient(linear, left top, left bottom,
            color-stop(0%,{$this->fields['header_bg1']}),
            color-stop(
               1%,{$this->fields['header_bg2']}), color-stop(20%,{$this->fields['header_bg3']}),
            color-stop(
               21%,{$this->fields['header_bg4']}), color-stop(40%,{$this->fields['header_bg4']}),
            color-stop(
               40%,{$this->fields['header_bg5']}), color-stop(63%,{$this->fields['header_bg5']}),
            color-stop(
               65%,{$this->fields['header_bg6']}), color-stop(99%,{$this->fields['header_bg6']})
            ); /* Chrome,Safari4+ */
         background: -webkit-linear-gradient(top,
            {$this->fields['header_bg1']} 0%,
            {$this->fields['header_bg2']} 2%,{$this->fields['header_bg3']} 20%,
            {$this->fields['header_bg4']} 21%,{$this->fields['header_bg4']} 40%,
            {$this->fields['header_bg5']} 40%,{$this->fields['header_bg5']} 63%,
            {$this->fields['header_bg6']} 65%,{$this->fields['header_bg6']} 99%
            ); /* Chrome10+,Safari5.1+ */
         background: -o-linear-gradient(top,
            {$this->fields['header_bg1']} 0%,
            {$this->fields['header_bg2']} 2%,{$this->fields['header_bg3']} 20%,
            {$this->fields['header_bg4']} 21%,{$this->fields['header_bg4']} 40%,
            {$this->fields['header_bg5']} 40%,{$this->fields['header_bg5']} 63%,
            {$this->fields['header_bg6']} 65%,{$this->fields['header_bg6']} 99%
            ); /* Opera 11.10+ */
         background: -ms-linear-gradient(top,
            {$this->fields['header_bg1']} 0%,
            {$this->fields['header_bg2']} 2%,{$this->fields['header_bg3']} 20%,
            {$this->fields['header_bg4']} 21%,{$this->fields['header_bg4']} 40%,
            {$this->fields['header_bg5']} 40%,{$this->fields['header_bg5']} 63%,
            {$this->fields['header_bg6']} 65%,{$this->fields['header_bg6']} 99%
            ); /* IE10+ */

         background: linear-gradient(to bottom,
            #ffffff 0%,
            {$this->fields['header_bg2']} 2%,{$this->fields['header_bg3']} 20%,
            {$this->fields['header_bg4']} 21%,{$this->fields['header_bg4']} 40%,
            {$this->fields['header_bg5']} 40%, {$this->fields['header_bg5']} 63%,
            {$this->fields['header_bg6']} 65%,{$this->fields['header_bg6']} 99%
            ); /* W3C */
      }

      .ext-ie div#header {
         background:{$this->fields['header_bg6']} url(\"fn_nav.png\") 0 0 repeat-x; /* IE6-9 */
      }

      #page {
         -moz-box-shadow: 0px 7px 10px {$this->fields['page_shadow_color']};
         -webkit-box-shadow: 0px 7px 10px {$this->fields['page_shadow_color']};
         box-shadow: 0px 7px 10px {$this->fields['page_shadow_color']};
      }

      #footer {
         -moz-box-shadow: 0px 7px 10px {$this->fields['footer_shadow_color']};
         -webkit-box-shadow: 0px 7px 10px {$this->fields['footer_shadow_color']};
         box-shadow: 0px 7px 10px {$this->fields['footer_shadow_color']};
         background: {$this->fields['footer_bg1']};
         background:-webkit-linear-gradient(top,
            {$this->fields['footer_bg1']}, {$this->fields['footer_bg2']});
         background:-moz-linear-gradient(top,
            {$this->fields['footer_bg1']}, {$this->fields['footer_bg2']});
         background:-o-linear-gradient(top,
            {$this->fields['footer_bg1']}, {$this->fields['footer_bg2']});
         background:linear-gradient(top,
            {$this->fields['footer_bg1']}, {$this->fields['footer_bg2']});
         filter: progid:DXImageTransform.Microsoft.gradient(
            startColorstr='{$this->fields['footer_bg1']}',
            endColorstr='{$this->fields['footer_bg2']}',GradientType=0 );
      }

      #debug h2, #debugajax h2 {
         border-left: 4px solid {$this->fields['menu_item_border']};
         border-bottom: 2px solid {$this->fields['menu_item_border']};
      }

      /*** TABS ***/

      .ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default {
         border: 1px solid {$this->fields['tabs_border']};  
         background: {$this->fields['tabs_bg2']};
         color: {$this->fields['tabs_title_color']};
      }

      .ui-state-active, .ui-widget-content .ui-state-active, .ui-widget-header .ui-state-active {
         border: 1px solid {$this->fields['tabs_border']};
         background: {$this->fields['tabs_bg1']};
         color: {$this->fields['tabs_title_color']};
      }

      .ui-widget-header {
         border: 1px solid {$this->fields['tabs_border']};  
         background: {$this->fields['tabs_bg3']};
         color: {$this->fields['tabs_title_color']};
      }
      ";
      return file_put_contents(CUSTOM_FILES_DIR."glpi_style.css", $CSS);
   }

   function post_purgeItem() {
      $this->add(self::defaultColors());
   }

   static function getSingle() {
      $style = new self;
      $tmp   = $style->find();
      $tmp   = array_shift($tmp);
      if (!empty($tmp)) {
         return $tmp['id'];
      }
      return -1;
   }

   static function defaultColors() {
      return array(
         'body'                  => '#dfdfdf',
         'button_bg_color'       => '#e1cc7b',
         'button_border'         => '#8B8468',
         'button_color'          => '#000000',
         'button_bg_color_hover' => '#ffffff',
         'button_border_hover'   => '#8B8468',
         'button_color_hover'    => '#000000',
         'text_color'            => '#000000',
         'link_color'            => '#659900',
         'link_hover_color'      => '#000000',
         'menu_link'             => '#000000',
         'ssmenu1_link'          => '#666666',
         'ssmenu2_link'          => '#000000',
         'link_topright'         => '#000000',
         'menu_border'           => '#9BA563',
         'menu_item_bg'          => '#f1e7c2',
         'menu_item_link'        => '#000000',
         'menu_item_border'      => '#CC9900',
         'menu_item_bg_hover'    => '#d0d99d',
         'table_bg_color'        => '#F2F2F2',
         'th'                    => '#e1cc7b',
         'th_text_color'         => '#000000',
         'tab_bg_1'              => '#f2f2f2',
         'tab_bg_1_2'            => '#cf9b9b',
         'tab_bg_2'              => '#f2f2f2',
         'tab_bg_2_2'            => '#cf9b9b',
         'tab_bg_3'              => '#e7e7e2',
         'tab_bg_4'              => '#e4e4e2',
         'tab_bg_5'              => '#f2f2f2',
         'header_bg1'            => '#FFFFFF',
         'header_bg2'            => '#f5efd6',
         'header_bg3'            => '#d6bc53',
         'header_bg4'            => '#c0cc7b',
         'header_bg5'            => '#d0d99d',
         'header_bg6'            => '#f1f4e3',
         'header_shadow_color'   => '#011E3A',
         'page_shadow_color'     => '#011E3A',
         'footer_shadow_color'   => '#011E3A',
         'footer_bg1'            => '#FFFFFF',
         'footer_bg2'            => '#e2cf83',
         'cadre_central_bg1'     => '#e8dab0',
         'cadre_central_bg2'     => '#FFFFFF',
         'tabs_bg1'              => '#fcfcfa',
         'tabs_bg2'              => '#ddddc8',
         'tabs_bg3'              => '#cfcfb2',
         'tabs_border'           => '#909058',
         'tabs_title_color'      => '#659900',
      );
   }
}
