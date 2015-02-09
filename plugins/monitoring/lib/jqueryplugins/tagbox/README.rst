TAGBOX - jQuery plugin for selection of predefined tags
=======================================================

**tagbox** is a jQuery plugin for creating a sleek *tags selection field* with autocomplete suggestions
while typing and a collapsible choices matrix for easy onscreen selection of one or multiple options.

Requirements
------------
`jQuery <http://jquery.com/>`_ (v1.10.2 is included with the example)


Usage
-----

Once you've added ``jquery.tagbox.js`` and ``jquery.tagbox.css`` to the list of script and style files that are loaded in your html page,
simply call the *tagbox* method on an empty element on the page:
::

    $('#tagbox-container').tagbox(options);

Options
~~~~~~~

In order for the **tagbox** to work properly, the only mandatory parameter is ``taglist`` which specifies the list of available tag choices to show.

.. csv-table::
   :header: "Name", "Type", "Default", "Description"
   :widths: 20, 10, 40, 200

   "taglist",      "list",   "empty", "List of optional tag choices"
   "selectedlis", "list",   "empty", "List of preselected tags"
   "cols",         "int",     5,      "Number of columns in the choices matrix"
   "expand",       "boolean", false,  "Expand choices matrix on initialization"
   "matrixlabel",  "html",    *see example*, "The caption of the link that shows the matrix"
   "matrixaltlabel",  "html",    *see example*, "The caption of the link that hides the matrix"
   "maxtags",       "int", 0 (unlimited),  "Maximum number of selected tags"
   "placeholder", "string", 'Select one or more...', "The placeholder for the manual input field"

.. note:: When the choices matrix is open, the autocomplete suggestions box is automatically turned off.

Example
~~~~~~~
::

    $('#tagbox-container').tagbox({
        taglist:        ['Spoon', 'Banana', 'Strawberry', 'Pillsberry', 'Blue Balloon', 'Alligator'],
        selectedlist:   ['Banana', 'Pillsberry'],
        cols:           3,
        maxtags:        4
    });



`DEMO <http://leandigo.github.io/tagbox/>`_

Events
------

*tagbox* exposes two events for hooking into the plugin functionality on tag addition and removal from the selected tags list.

.. csv-table::
   :header: "Event", "Description"
   :widths: 15, 300

   "tagAdded", "Triggered when a tag is added to the selected tags list"
   "tagRemoved", "Triggered when a tag is removed from the selected tags list"

Example
~~~~~~~
::

    $('#tagbox-container').on('tagAdded', function() {
        // Do something...
    });

Data
----

You can access the currently selected tags list at anytime by querying the ``data`` property of the ``.tagbox`` element.
::

    $('.tagbox').data('selected');


Keyboard Bindings
-----------------

* When the autocomplete suggestions box is shown you can use the Up and Down arrow keys on the keyboard to navigate between the choices
* When the choices matrix is shown, you can use the Up and Down arrow keys to navigate between the choices that match the string you've entered
* You can use Backspace to delete already selected tags, when your cursor is focused on the input field.

License
-------
Copyright (c) 2013, Leandigo (|leandigo|_)

Released under the MIT License. See the LICENSE file for details.

.. |leandigo| replace:: www.leandigo.com
.. _leandigo: http://www.leandigo.com

.. figure:: https://cruel-carlota.pagodabox.com/5be962a5950f388bc92f9a93888c8bc3
   :alt: Githalytics
   :target: http://githalytics.com/leandigo/tagbox
