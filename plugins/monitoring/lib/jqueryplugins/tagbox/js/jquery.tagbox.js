/*
    TAGBOX - jQuery plugin for selection of predefined tags
    --------------------------------------------------------------

    Copyright (C) 2013, Leandigo (www.leandigo.com)

    This code is released under the MIT License:

    Permission is hereby granted, free of charge, to any person obtaining a copy of
    this software and associated documentation files (the "Software"), to deal in
    the Software without restriction, including without limitation the rights to
    use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
    of the Software, and to permit persons to whom the Software is furnished to do
    so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in all
    copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
    FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
    COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
    IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
    CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

*/

(function($) {
    $.fn.tagbox = function(options) {
        // Options and Defaults:
        // Predefined tag list
        options.taglist         = options.taglist       || [];
        // Values of selected tags
        options.selectedlist    = options.selectedlist  || [];
        // Columns
        options.cols            = options.cols          || 5;
        // Expand on init
        options.expand          = options.expand        || false;
        // The caption of the link that shows the matrix
        options.matrixlabel     = options.matrixlabel   || 'Click here to see all available choices <span class="tagbox-matrix-label-arrow">[+]</span>';
        // The caption of the link that hides the matrix
        options.matrixaltlabel  = options.matrixaltlabel || 'Click here to close the choices list <span class="tagbox-matrix-label-arrow">[&minus;]</span>'
        // Maximum number of selected tags
        options.maxtags         = options.maxtags       || 0;
        // The placeholder for the manual input field
        options.placeholder     = options.placeholder   || 'Select one or more...';

        // jQuery-selected DOM Elements:
        // The TagBox element
        var $el                 = div({class:'tagbox'})
        // The manual input field
        ,   $input              = input({'class' : 'tagbox-field-input', 'placeholder' : options.placeholder})
        // The auto-completion dropdown
        ,   $dropdown           = div({'class' : 'tagbox-dropdown'})
        // The wrapper for the input field and the dropdown
        ,   $inputwrap          = div({'class' : 'tagbox-input-wrapper'}).append($input, $dropdown)
        // The main field that contains selected tags
        ,   $field              = div({'class' : 'tagbox-field'}).append($inputwrap)
        // The matrix
        ,   $matrix             = div({'class' : 'tagbox-matrix'})
        // The matrix toggle link
        ,   $matrixlabel        = div({'class' : 'tagbox-matrix-label'}).html(options.matrixlabel)

        // Variables:
        // Tags matching manual input
        ,   matching_tags       = []
        // Are there tags matching the input
        ,   matching            = false
        // Indices of selected tags
        ,   selected            = []
        ;

        // ======== INITIALIZATION ========
        // Initial positioning and construction of the element
        width = $(this).width() > 0 ? $(this).width() - 12 : $(this).parent().width() - 12;
        $matrix.width(width);
        $el.append($field, $matrixlabel, $matrix);
        $(this).append($el);

        // Hide matrix
        $matrix.slideUp(0);
        // Toggle the matrix' visibility
        $matrixlabel.click(function() {
            $matrixlabel.html(options.matrixlabel == $matrixlabel.html()? options.matrixaltlabel : options.matrixlabel);
            options.expand = !options.expand;
            options.expand ? $matrix.slideDown('fast', function() { $dropdown.slideUp(0); }) : $matrix.slideUp('fast');
        });

        // Hack to show matrix on init if necessary
        if (options.expand) {
            options.expand = !options.expand;
            $matrixlabel.click();
        }

        // Hide completion dropdown
        $dropdown.slideUp(0);

        // Initialize matrix
        for (var ix in options.taglist) {
            $dropdown.append(clickable_tag(ix).slideUp(0));
            $matrix.append(matrix_item(ix));
            $('.tagbox-matrix-item', $matrix).css({
                width: $matrix.width() / (options.cols + 0.2)
            });
        }

        // Initialize selected tags
        var initlist = options.selectedlist
        while (initlist.length) add_to_selected(options.taglist.indexOf(initlist.pop()));


        // ========= KEYBOARD BINDINGS ================
        // keyUp
        $input.keyup(function(event) {
            var e   = event.originalEvent
            ,   str = $input.val()
            ;

            // Try to match tags with input field
            match_tags(str);

            // Reset previously matching tags
            $('.tagbox-matching').removeClass('tagbox-matching');

            // Reset matching substring styling inside matrix
            $('.tagbox-clickable-tag', $matrix).each(function(ix, el, arr) {
                $(el).text(options.taglist[$(el).data('tag-ix')]);
            });

            // If matching tags found
            if (matching) {
                // Go over all the tags inside completion dropdown and matrix
                $dropdown.children().each(function(ix, el, arr) {
                    // For matching tags
                    if ($(el).data('tag-ix') in matching_tags) {
                        // Show tag in dropdown
                        $(el).html(matching_tags[ix].html).slideDown('fast').addClass('tagbox-matching');
                        // Highlight matching tags in matrix and emphasize matching substrings
                        $($('.tagbox-clickable-tag', $matrix)[ix])
                        .html(matching_tags[ix].html)
                        .addClass('tagbox-matching');
                    } else {
                        // Hide unmatching tags from completion dropdown
                        $(el).slideUp('fast');
                    }
                });
                // If matrix is hidden, show dropdown
                options.expand || $dropdown.slideDown('fast');

            // No matching tags
            } else {
                // If SPACE pressed, delete entered text
                if (e.which == 32) $input.fadeOut('fast', function() { $input.val(''); $input.show(); });
                // Hide dropdown
                $dropdown.slideUp('fast');
                // Remove matching styling from matrix
                $('.tagbox-clickable-tag.selected', $el).removeClass('selected');
            }
        });

        // keyDown
        $input.keydown(function(event) {
            var e           = event.originalEvent
            ;

            // UP or DOWN - selecting a tag from matching tags in dropdown or matrix
            if (e.which == 38 || e.which == 40) {
                e.preventDefault();
                // If there are no tags matching the input, do nothing
                if (!matching) return;

                // Determine direction (1 or -1)
                var dir         = e.which - 39
                // Find all visible items in dropdown
                ,   $items      = $('.tagbox-matching:visible', $el)
                // Reset styling
                ,   $selected   = $('.tagbox-clickable-tag.selected').removeClass('selected')
                // Find which in dropdown/matrix was selected earlier
                ,   selector_ix = $items.index($selected)
                // Index of item to be selected next
                ,   ix          = dir + selector_ix
                ;

                // Cycle on edges
                if (ix >= $items.length)    ix = 0;
                if (ix < 0)                 ix = $items.length - 1;
                // Set the style to mark selected item
                $($items[ix]).addClass('selected');

            // BACKSPACE - if input field is empty will remove the last tag added to field
            } else if (e.which === 8) {
                $input.val().length || remove_from_selected($('.tagbox-selected-tag', $el).last().data('tag-ix'));

            // RETURN - Select a tag (from dropdown/matrix if selected using UP/DOWN or the typed tag if matches exactly)
            } else if (e.which == 13) {
                // Find the index of the selected tag
                var ix = $('.tagbox-clickable-tag.selected').data('tag-ix');
                // If there is a selected tag add it
                if (ix || ix === 0) add_to_selected(ix);
                // Otherwise, check if there's an exact match.
                else if (!add_if_exact()) {
                    // If there's no exact match delete the value from the input and hide the dropdown
                    $input.val($input.val() + ' ');
                    $input.fadeOut('fast', function() { $input.val(''); $input.show(); });
                    $dropdown.slideUp('fast');
                }

            // SPACE - If there's an exact match in the input field, add the tag. Otherwise, delete value
            } else if (e.which == 32) {
                add_if_exact(true) && e.preventDefault();
            }
        });


        $('.tagbox-matrix-checkbox').change(function() {
            var ix = $(this).data('tag-ix');
            $(this).prop('checked') ? add_to_selected(ix) : remove_from_selected(ix);
        });

        return $el;

        function add_if_exact(single) {
            single      = single || false;
            var exact   = -1
            ,   cnt     = 0
            ;
            for (var ix in matching_tags) {
                if (exact == -1) exact = matching_tags[ix].exact && ix;
                cnt++;
            }
            if (parseInt(exact, 10) > -1 && (!single || (single && cnt == 1))) {
                add_to_selected(exact);
                return true;
            }
            return false;
        }

        function repopulate_selected() {
            options.selectedlist = [];
            for (ix in selected) options.selectedlist.push(options.taglist[ix]);
            $el.data('selected', options.selectedlist);
        }

        function add_to_selected(ix) {
            if (options.maxtags && options.selectedlist.length >= options.maxtags) {
                remove_from_selected(ix);
                return;
            }
            if (!ix || !(ix >= 0 && ix < options.taglist.length) || selected[ix]) return;
            selected[ix] = selected_tag(ix);
            $input.val('');
            $inputwrap.before(selected[ix]);
            $('.tagbox-matrix-checkbox', $matrix)[ix].checked = true;
            repopulate_selected();
            $el.trigger('tagAdded', [options.taglist[ix], options.selectedlist]);
        }

        function remove_from_selected(ix) {
            if (!selected[ix]) return;
            selected[ix].animate(
                {width: 0, padding: 0, opacity: 0}, 'fast', $.proxy(function() { this.remove(); }, selected[ix])
            );
            delete selected[ix];
            $($('.tagbox-matrix-checkbox')[ix]).prop('checked', false);
            $input.val('');
            repopulate_selected();
            $el.trigger('tagRemoved', [options.taglist[ix], options.selectedlist]);
        }

        function selected_tag(ix) {
            return div({'class' : 'tagbox-selected-tag'})
            .data('tag-ix', ix)
            .html(options.taglist[ix])
            .append(
                span({'class' : 'tagbox-selected-tag-remove'})
                .html('&nbsp;&times;')
                .click(function() { remove_from_selected($(this).parent().data('tag-ix')); })
            );
        }

        function clickable_tag(ix) {
            return div({'class' : 'tagbox-clickable-tag'})
            .data('tag-ix', ix)
            .click(function(e) {
                add_to_selected($(this).data('tag-ix'));
                $dropdown.slideUp('fast');
            });
        }

        function matrix_item(ix) {
            return div({'class' : 'tagbox-matrix-item'})
            .data('tag-ix', ix)
            .append(
                input({'class' : 'tagbox-matrix-checkbox', 'type' : 'checkbox'}).data('tag-ix', ix),
                div({'class' : 'tagbox-matrix-tag'}).append(clickable_tag(ix).text(options.taglist[ix]))
            );
        }

        function match_tags(str) {
            matching_tags = [];
            matching = false;
            if (!str) return;
            for (var ix in options.taglist) {
                var tag = options.taglist[ix]
                ,   pos = tag.toLowerCase().indexOf(str.toLowerCase())
                ,   txt = [tag.slice(0, pos), tag.substr(pos, str.length), tag.slice(pos + str.length)]
                ;
                if (pos > -1 && !(ix in selected)) {
                    matching = true;
                    matching_tags[ix] = {
                        html: txt[0] + span({'class': 'tagbox-match'}).text(txt[1]).outerHTML() + txt[2],
                        exact: str.toLowerCase() == tag.toLowerCase()
                    };
                }
            }
        }
    };

    function div(attr) {
        return $(document.createElement('div')).attr(attr);
    }

    function input(attr) {
        return $(document.createElement('input')).attr(attr);
    }

    function span(attr) {
        return $(document.createElement('span')).attr(attr);
    }

})(jQuery);

// OuterHTML - in case somebody is wondering.
jQuery.fn.outerHTML = function(s) {
    return s
        ? this.before(s).remove()
        : jQuery("<p>").append(this.eq(0).clone()).html();
};