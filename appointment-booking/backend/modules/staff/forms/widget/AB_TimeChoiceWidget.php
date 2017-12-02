<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AB_TimeChoiceWidget
{
    /**
     * @var array
     */
    protected $values = array();

    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct( array $options = array() )
    {
        // Handle widget options.
        $options = array_merge( array(
            'use_empty' => true,
            'empty_value' => null,
            'exclude_last_slot' => false,
        ), $options );

        // Insert empty value if required.
        if ( $options['use_empty'] ) {
            $this->values[ null ] = $options['empty_value'];
        }

        $ts_length  = AB_BookingConfiguration::getTimeSlotLength();
        $time_start = AB_StaffScheduleItem::WORKING_START_TIME;
        $time_end   = AB_StaffScheduleItem::WORKING_END_TIME;

        if ( $options['exclude_last_slot'] ) {
            $time_end -= $ts_length;
        }

        // Run the loop.
        while ( $time_start <= $time_end ) {
            $this->values[ AB_DateTimeUtils::buildTimeString( $time_start ) ] = AB_DateTimeUtils::formatTime( $time_start );
            $time_start += $ts_length;
        }
    }

    /**
     * Render the widget.
     *
     * @param       $name
     * @param null  $value
     * @param array $attributes
     *
     * @return string
     */
    public function render( $name, $value = null, array $attributes = array() )
    {
        $options = array();
        $attributes_str = '';
        foreach ( $this->values as $option_value => $option_text ) {

            $selected = strval( $value ) == strval( $option_value );
            $options[ ] = sprintf(
                '<option value="%s"%s>%s</option>',
                $option_value,
                ($selected ? ' selected="selected"' : ''),
                $option_text
            );
        }
        foreach ( $attributes as $attr_name => $attr_value ) {
            $attributes_str .= sprintf( ' %s="%s"', $attr_name, $attr_value );
        }

        return sprintf( '<select name="%s"%s>%s</select>', $name, $attributes_str, implode( '', $options ) );
    }

    /**
     * @param $start
     * @param string $selected
     * @return array
     */
    public function renderOptions( $start, $selected = '' )
    {
        $options = array();
        foreach ( $this->values as $option_value => $option_text ) {
            if ( $start && strval( $option_value ) < strval( $start ) ) continue;
            $options[ ] = sprintf(
                '<option value="%s"%s>%s</option>',
                $option_value,
                (strval( $selected ) == strval( $option_value ) ? 'selected="selected"' : ''),
                $option_text
            );
        }

        return $options;
    }
}