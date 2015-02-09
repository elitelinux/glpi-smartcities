/*
 -------------------------------------------------------------------------
 Shellcommands plugin for GLPI
 Copyright (C) 2014 by the Shellcommands Development Team.
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Shellcommands.

 Shellcommands is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Shellcommands is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Shellcommands. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------  */


/** 
*  Init shellcommands advanced execution
*  
* @param string root_doc
* @param string toobserve
* @param string toupdate
*/
function shellcommand_advanced_execution(root_doc, toobserve, toupdate){
   
   var command_group = $("form[name='"+toobserve+"'] input[name='plugin_shellcommands_commandgroups_id']").val();
   var items = $("form[name='"+toobserve+"'] div[id^='custom_values']");

   var items_to_execute = {};

   $.each(items, function(index, option) {
      var itemtype = $("div[id='"+option.id+"'] select[name='items']");
      var items_id = $("div[id='"+option.id+"'] input[name='items_id']");
      if (itemtype != undefined && items_id != undefined) {
         items_to_execute[index] = {'itemtype' : itemtype.val(), 'items_id' : items_id.val()};
      }
   });
   
   var params = {'command_type'     : 'PluginShellcommandsAdvanced_Execution', 
                 'command_group'    : command_group, 
                 'items_to_execute' : JSON.stringify(items_to_execute)};
   
   shellcommandsActions(root_doc, toupdate, params);
}

/** 
*  Init shellcommands ajax actions
*  
* @param string root_doc
* @param string toupdate
* @param string params
*/
function shellcommandsActions(root_doc, toupdate, params){
   if(toupdate != ''){
      var item_bloc = $('#'+toupdate);
      // Loading
      item_bloc.html('<div style="width:100%;text-align:center"><img src="'+root_doc+'/plugins/shellcommands/pics/large-loading.gif"></div>');
   }

   // Send data
   $.ajax({
      url: root_doc+'/plugins/shellcommands/ajax/shellcommand.exec.php',
      type: "POST",
      dataType: "html",
      data: params,
           
      success: function(response, opts) {
         if(toupdate != ''){
            item_bloc.html(response);
         }
      }
   });
}

/**
 * changeNbValue : display text input 
 * 
 * @param newValue 
 */
function changeNbValue(newValue) {
   document.getElementById('nbValue').value = newValue;
   return true;
}


/**
 * shellcommands_add_custom_values : add text input 
 */
function shellcommands_add_custom_values(field_id, root_doc){
   var count = $('#count_custom_values').val();
   $('#count_custom_values').val(parseInt(count)+1);

   $.ajax({
      url: root_doc+'/plugins/shellcommands/ajax/addnewvalue.php',
      type: "POST",
      dataType: "html",
      data: { 
         'action' : 'add',
         'count'  : $('#count_custom_values').val()
      },
      success: function(response, opts) {
         var item_bloc = $('#'+field_id);
         item_bloc.append(response);

         var scripts, scriptsFinder=/<script[^>]*>([\s\S]+?)<\/script>/gi;
         while(scripts=scriptsFinder.exec(response)) {
            eval(scripts[1]);
         }
      }
   });
}

/**
 * shellcommands_delete_custom_values : delete text input 
 * 
 * @param field_id  
 */
function shellcommands_delete_custom_values(field_id){
   var count = $('#count_custom_values').val();
   if(count > 1){
      $('#'+field_id+count).remove();
      $('#count_custom_values').val(parseInt(count)-1);
   }
}
