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

namespace mod_dialogue\form;

defined('MOODLE_INTERNAL') || die();

class conversation_form extends message_form {
    protected function definition() {
        $data = $this->_customdata['data'];

        if (empty($data['cmid'])) {
            throw new \moodle_exception('Course module identifier required!');
        }

        $mform = $this->_form;

        $mform->disable_form_change_checker();

        $mform->addElement('header', 'openwithsection', get_string('openwith', 'dialogue'));


        $html = '<div class="fitem fitem_ftext">
                 <div class="fitemtitle"></div>
                 <div class="felement ftext">
                 <div id="rp-selected"></div>
                 <input class="" type="text" placeholder="Recipient\'s name" id="rp-input">
                 </div>
                 </div>';

        $mform->addElement('html', $html);

        $mform->addElement('header', 'messagesection', get_string('message', 'dialogue'));

        $mform->addElement('text', 'subject', get_string('subject', 'dialogue'), array('class'=>'input-xxlarge'));
        $mform->setType('subject', PARAM_TEXT);

        $mform->setExpanded('messagesection', true);

        parent::definition();
    }
    public function definition_after_data() {
        global $CFG, $PAGE;

        parent::definition_after_data();

        $data = $this->_customdata['data'];
        $this->set_data(array('id' => $data['conversationid']));
        $this->set_data(array('subject' => $data['subject']));
        $ajaxurl = $CFG->wwwroot . '/mod/dialogue/searchpotentials.json.php?q={query}&id=' . $data['cmid'] . '&sesskey=' . sesskey();
        $participants = array_values($data['receivers']); // Need to remove php array keys. TODO research PHP array
        $arguments =
            array(
                array('inputNode' => '#rp-input',
                    'source' => $ajaxurl,
                    'maxResults' => 5,
                    'selectedNode' => '#rp-selected',
                    'selectedHiddenName' => 'openwith',
                    'selectedItems' => $participants,
                )
            );
        // Add the recipient picker after data as need to be rendered.
        $PAGE->requires->yui_module('moodle-mod_dialogue-recipientpicker', 'M.mod_dialogue.recipientpicker.init', $arguments, null, true);
    }

    public function get_submitted_data() {
        $data = parent::get_submitted_data();

        $data->participants = optional_param_array('openwith', array(), PARAM_INT);

        return $data;
    }
}