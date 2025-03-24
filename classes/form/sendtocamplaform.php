<?php
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
 * Form to send a quiz configuration to CAMPLA
 *
 * @package    quizaccess_campla
 * @author     Luca Bösch <luca.boesch@bfh.ch>
 * @copyright  2024 BFH Bern University of Applied Sciences
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace quizaccess_campla\form;

defined('MOODLE_INTERNAL') || die;

use quizaccess_campla\lib;
use quizaccess_campla\settings_provider;
use function Symfony\Component\Translation\t;

require_once(__DIR__ . '/../../../../../../config.php');
require_once($CFG->libdir . '/filelib.php');

require_login();

/**
 * Form to send a quiz configuration to CAMPLA
 *
 * @package    quizaccess_campla
 * @copyright  2024 BFH Bern University of Applied Sciences
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sendtocamplaform extends \core_form\dynamic_form {

    /**
     * Returns form context
     *
     * If context depends on the form data, it is available in $this->_ajaxformdata or
     * by calling $this->optional_param()
     *
     * @return \context
     */
    protected function get_context_for_dynamic_submission(): \context {
        return \context_system::instance();
    }

    /**
     * Check if current user has access to this form, otherwise throw exception
     *
     */
    protected function check_access_for_dynamic_submission(): void {
        global $COURSE;
        $context = \context_course::instance($COURSE->id);
        require_capability('quizaccess/campla:canusecampla', $context);
    }

    /**
     * Load in existing data as form defaults
     *
     */
    public function set_data_for_dynamic_submission(): void {
        $this->set_data([
            'hidebuttons' => $this->optional_param('hidebuttons', false, PARAM_BOOL),
        ]);
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     *
     * This method can return scalar values or arrays that can be json-encoded, they will be passed to the caller JS.
     *
     * Submission data can be accessed as: $this->get_data()
     *
     * @return ['status' => int, 'message'=> string]
     */
    public function process_dynamic_submission() {
        $formdata = $this->get_data();
        lib::init();
        $success = lib::sendtocampla($formdata);

        switch ($success) {
            case 201:
                return [
                    'status' => 200,
                    'message' => get_string('sendtocamplasuccesscreate', 'quizaccess_campla'),
                ];
            case 200:
                return [
                    'status' => 200,
                    'message' => get_string('sendtocamplasuccessupdate', 'quizaccess_campla'),
                ];
            case 400:
                return [
                    'status' => 201,
                    'message' => get_string('sendtocamplafailforbidden', 'quizaccess_campla'),
                ];
            default:
                return [
                    'status' => 500,
                    'message' => get_string('sendtocamplafail', 'quizaccess_campla'),
                ];
        }
    }


    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * This is used in the form elements sensitive to the page url, such as Atto autosave in 'editor'
     *
     * @return \moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): \moodle_url {
        return new \moodle_url('/mod/quiz/accessrule/campla/');
    }

    /**
     * Form definition
     *
     * @return void
     */
    public function definition() {
        $this->_ajaxformdata['cmid'];
        global $CFG, $USER;
        $mform = $this->_form;

        $mform->addElement('text', 'quizname', get_string('quizname', 'quizaccess_campla'));
        $mform->setType('quizname', PARAM_NOTAGS);
        $mform->addRule('quizname', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'coursename', get_string('coursename', 'quizaccess_campla'));
        $mform->setType('coursename', PARAM_NOTAGS);
        $mform->addRule('coursename', get_string('required'), 'required', null, 'client');

        // Supported lockdown tools
        $camplasecurityleveloptions = array(
            '5' => 'SafeExamBrowser',
            '1' => 'Lernstick',
        );
        $mform->addElement('select', 'camplasecuritylevel', get_string('camplasecuritylevel', 'quizaccess_campla'), $camplasecurityleveloptions);
        $mform->setDefault('camplasecuritylevel', '5');
        $mform->setType('camplasecuritylevel', PARAM_NOTAGS);

        $mform->addElement('text', 'quizurl', get_string('quizurl', 'quizaccess_campla'));
        $mform->setType('quizurl', PARAM_URL);

        //TODO: Should only be showed if security level 5 is selected -> SafeExamBrowser
        $mform->addElement('password', 'camplasebexitpassword', get_string('camplasebexitpassword', 'quizaccess_campla'));
        $mform->setType('camplasebexitpassword', PARAM_RAW);

        $mform->addElement('text', 'quizopens', get_string('quizopens', 'mod_quiz'));
        $mform->setType('quizopens', PARAM_NOTAGS);

        $mform->addElement('text', 'quizcloses', get_string('quizcloses', 'mod_quiz'));
        $mform->setType('quizcloses', PARAM_NOTAGS);

        $mform->addElement('hidden', 'quizstarturl');
        $mform->setType('quizstarturl', PARAM_URL);

        $mform->addElement('hidden', 'quizopensunixtime');
        $mform->setType('quizopensunixtime', PARAM_INT);

        $mform->addElement('hidden', 'quizclosesunixtime');
        $mform->setType('quizclosesunixtime', PARAM_INT);

        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);

        $mform->freeze(['quizurl', 'quizopens', 'quizcloses']);

        if (empty($this->_ajaxformdata['hidebuttons'])) {
            $this->add_action_buttons(true, get_string('submitlabel', 'quizaccess_campla'));
        }

        $cmid = isset($this->_ajaxformdata['cmid']) ? (int) $this->_ajaxformdata['cmid'] : 0;
        $quizname = \quizaccess_campla\settings_provider::get_campla_quizname($cmid);
        $coursename = \quizaccess_campla\settings_provider::get_campla_coursename($cmid);
        $quizurl = new \moodle_url('/mod/quiz/view.php', ['id' => $cmid]);
        if (\quizaccess_campla\settings_provider::get_campla_timeopen($cmid) !== 0) {
            $quizopens = \core_date::strftime(get_string('strftimerecentfull', 'langconfig'),
                \quizaccess_campla\settings_provider::get_campla_timeopen($cmid));
            $quizopensunixtime = \quizaccess_campla\settings_provider::get_campla_timeopen($cmid);
        } else {
            $quizopens = get_string('na', 'quizaccess_campla');
            $quizopensunixtime = 0;
        }
        if (\quizaccess_campla\settings_provider::get_campla_timeclose($cmid) !== 0) {
            $quizcloses = \core_date::strftime(get_string('strftimerecentfull', 'langconfig'),
                \quizaccess_campla\settings_provider::get_campla_timeclose($cmid));
            $quizclosesunixtime = \quizaccess_campla\settings_provider::get_campla_timeclose($cmid);
        } else {
            $quizcloses = get_string('na', 'quizaccess_campla');
            $quizclosesunixtime = 0;
        }

        $this->set_data([
            'quizname' => $quizname,
            'coursename' => $coursename,
            'quizurl' => $quizurl,
            'quizstarturl' => $quizurl,
            'quizopens' => $quizopens,
            'quizcloses' => $quizcloses,
            'camplasecuritylevel' => $camplasecuritylevel,
            'camplasebexitpassword' => $camplasebexitpassword,
            'quizopensunixtime' => $quizopensunixtime,
            'quizclosesunixtime' => $quizclosesunixtime,
            'cmid' => $cmid,
            'hidebuttons' => $this->optional_param('hidebuttons', false, PARAM_BOOL),
        ]);
    }
}
