// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This is an empty module, that is required before all other modules.
 * Because every module is returned from a request for any other module, this
 * forces the loading of all modules with a single request.
 *
 * @module     mod_katest/katest
 * @package    mod_katest
 * @copyright  2016 Joseph Gilgen <gilgenlabs@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      2.9
 */
define(['jquery', 'core/ajax', 'core/notification'], function($, ajax, notification) {
    return /** @alias module:mod_katest/katest */ {

        setButtons: function(){
            $('.katest-skill-button').click(function() {
              $(this).addClass('katest-disabled');
            });
        },

        submitTest: function(){
          $('#katest-submit-button').click(function() {
            var time = Math.floor(Date.now() / 1000);
            $("input[name='timesubmitted']").val(time);
            $("#katest-get-results-form").submit();
          });
        },

        deleteAttempt: function(){
          $('.katest-delete-attempt').click(function() {
            var $this = $(this);
            var userid = $this.data('userid');
            var katestid = $this.data('katestid');
            var attemptid = $this.data('attemptid');

            // First - reload the data for the page.
            var promises = ajax.call([{
                methodname: 'mod_katest_delete_attempt',
                args:{
                  userid:userid,
                  katestid:katestid,
                  attemptid:attemptid
                }
            }]);
            promises[0].done(function() {
              var $html = $('<div class="katest-attempt-delete-message">Attempt was deleted.</div>');
              $('#katest-attempt-'+attemptid).replaceWith($html);
              setTimeout(function(){
                $html.fadeOut(400);
              }, 500);

            }).fail(notification.exception);

          });
        }
    };
});
