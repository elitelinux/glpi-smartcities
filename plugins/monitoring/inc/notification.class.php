<?php

/*
   ------------------------------------------------------------------------
   Plugin Monitoring for GLPI
   Copyright (C) 2011-2014 by the Plugin Monitoring for GLPI Development Team.

   https://forge.indepnet.net/projects/monitoring/
   ------------------------------------------------------------------------

   LICENSE

   This file is part of Plugin Monitoring project.

   Plugin Monitoring for GLPI is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   Plugin Monitoring for GLPI is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with Monitoring. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   Plugin Monitoring for GLPI
   @author    David Durieux
   @co-author
   @comment
   @copyright Copyright (c) 2011-2014 Plugin Monitoring for GLPI team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/monitoring/
   @since     2011

   ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMonitoringNotification {


   static function test() {
      echo "<script language='Javascript'>

Ext.ux.NotificationMgr = {
    positions: []
};

Ext.ux.Notification = Ext.extend(Ext.Window, {
    initComponent: function(){
          Ext.apply(this, {
            iconCls: this.iconCls || 'x-icon-information',
            width: 200,
            autoHeight: true,
            closable: false,
            plain: false,
            draggable: false,
            bodyStyle: 'text-align:center;padding:1em;'
          });
          if(this.autoDestroy) {
            this.task = new Ext.util.DelayedTask(this.hide, this);
          } else {
              this.closable = true;
          }
        Ext.ux.Notification.superclass.initComponent.call(this);
    },
    setMessage: function(msg){
        this.body.update(msg);
    },
    setTitle: function(title, iconCls){
        Ext.ux.Notification.superclass.setTitle.call(this, title, iconCls||this.iconCls);
    },
    onRender:function(ct, position) {
        Ext.ux.Notification.superclass.onRender.call(this, ct, position);
    },
    onDestroy: function(){
        Ext.ux.NotificationMgr.positions.remove(this.pos);
        Ext.ux.Notification.superclass.onDestroy.call(this);
    },
    afterShow: function(){
        Ext.ux.Notification.superclass.afterShow.call(this);
        this.on('move', function(){
               Ext.ux.NotificationMgr.positions.remove(this.pos);
               if(this.autoDestroy) {
                this.task.cancel();
               }
        }, this);
        if(this.autoDestroy) {
            this.task.delay(this.hideDelay || 5000);
       }
    },
    animShow: function(){
        this.pos = 0;
        while(Ext.ux.NotificationMgr.positions.indexOf(this.pos)>-1)
            this.pos++;
        Ext.ux.NotificationMgr.positions.push(this.pos);
        this.setSize(200,100);
        this.el.alignTo(document, 'br-br', [ -20, -5-((this.getSize().height+10)*this.pos) ]);
        this.el.slideIn('b', {
            duration: 1,
            callback: this.afterShow,
            scope: this
        });
    },
    animHide: function(){
           Ext.ux.NotificationMgr.positions.remove(this.pos);
           this.el.disableShadow();
        this.el.ghost('b', {
            duration: 1,
            remove: true
        });
    },
    focus: Ext.emptyFn
});


function toastAlert( the_title, the_message ) {
    // Set defaults for the toast window title and icon
    the_title = typeof(the_title) != 'undefined' ? the_title : 'Notice';

    // Create the toast window
new Ext.ux.Notification({
                iconCls:    'x-icon-error',
                title:      the_title,
                html:       the_message,
                autoDestroy: true,
                hideDelay:  5000
            }).show(document);
} // eo function toastAlert

toastAlert('Critical!', 'Apache on server xxx is down...');


</script>";
   }
}

?>