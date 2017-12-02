<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AB_BusinessHoursForm extends AB_Form {

    public function __construct() {
        $this->setFields(array(
            'ab_settings_monday_start',
            'ab_settings_monday_end',
            'ab_settings_tuesday_start',
            'ab_settings_tuesday_end',
            'ab_settings_wednesday_start',
            'ab_settings_wednesday_end',
            'ab_settings_thursday_start',
            'ab_settings_thursday_end',
            'ab_settings_friday_start',
            'ab_settings_friday_end',
            'ab_settings_saturday_start',
            'ab_settings_saturday_end',
            'ab_settings_sunday_start',
            'ab_settings_sunday_end',
        ));
    }

    public function save() {

        foreach ( $this->data as $field => $value ) {
            update_option( $field, $value );
        }
    }

    /**
     * @param string $field_name
     * @param bool $is_start
     * @return string
     */
    public function renderField($field_name = 'ab_settings_monday', $is_start = true) {

        $ts_length      = AB_BookingConfiguration::getTimeSlotLength();
        $time_output    = AB_StaffScheduleItem::WORKING_START_TIME;
        $time_end       = AB_StaffScheduleItem::WORKING_END_TIME;
        $option_name    = $field_name . ( $is_start ? '_start' : '_end' );
        $class_name     = $is_start ? 'select_start' : 'select_end';
        $selected_value = get_option( $option_name );
        $output         = "<select name={$option_name} class={$class_name}>";

        if ( $is_start ) {
            $output .= "<option value=''>" . __( 'OFF','ab' ) . "</option>";
            $time_end -= $ts_length;
        }

        while ( $time_output <= $time_end ) {
            $value    = AB_DateTimeUtils::buildTimeString( $time_output, false );
            $op_name  = AB_DateTimeUtils::formatTime( $time_output );
            $selected = $value == $selected_value ? ' selected="selected"' : '';
            $output  .= "<option value='{$value}'{$selected}>{$op_name}</option>";
            $time_output += $ts_length;
        }

        $output .= '</select>';

        return $output;
    }
}