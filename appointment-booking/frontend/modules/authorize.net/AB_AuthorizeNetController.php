<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_AuthorizeNetController
 */
class AB_AuthorizeNetController extends AB_Controller
{
    protected function getPermissions()
    {
        return array(
            '_this' => 'anonymous',
        );
    }

    /**
     * Do AIM payment.
     */
    public function executeAuthorizeNetAIM()
    {
        include_once AB_PATH . '/lib/payment/authorize.net/autoload.php';

        $response = null;
        $userData = new AB_UserBookingData( $this->getParameter( 'form_id' ) );

        if ( $userData->load() ) {
            define( "AUTHORIZENET_API_LOGIN_ID", get_option( 'ab_authorizenet_api_login_id' ) );
            define( "AUTHORIZENET_TRANSACTION_KEY", get_option( 'ab_authorizenet_transaction_key' ) );
            define( "AUTHORIZENET_SANDBOX", (bool)get_option( 'ab_authorizenet_sandbox' ) );

            $price = $userData->getFinalServicePrice() * $userData->get('number_of_persons');

            $sale             = new AuthorizeNetAIM();
            $sale->amount     = $price;
            $sale->card_num   = $this->getParameter( 'ab_card_number' );
            $sale->card_code  = $this->getParameter( 'ab_card_code' );
            $sale->exp_date   = $this->getParameter( 'ab_card_month' ) . '/' . $this->getParameter( 'ab_card_year' );
            $sale->first_name = $userData->get( 'name' );
            $sale->email      = $userData->get( 'email' );
            $sale->phone      = $userData->get( 'phone' );

            $response = $sale->authorizeAndCapture();
            if ( $response->approved ) {
                /** @var AB_Appointment $appointment */
                $appointment = $userData->save();

                $customer_appointment = new AB_CustomerAppointment();
                $customer_appointment->loadBy( array(
                    'appointment_id' => $appointment->get('id'),
                    'customer_id'    => $userData->getCustomerId()
                ) );

                $payment = new AB_Payment();
                $payment->set( 'total', $price);
                $payment->set( 'type', 'authorizeNet' );
                $payment->set( 'customer_appointment_id', $customer_appointment->get( 'id' ) );
                $payment->set( 'created', current_time( 'mysql' ) );
                $payment->save();

                $response = array ( 'state' => 'success' );
            } else {
                $response = array ( 'status' => 'error', 'error' => $response->response_reason_text );
            }
        } else {
            $response = array( 'status' => 'error', 'error' => __( 'Session error.', 'ab' ) );
        }

        // Output JSON response.
        header( 'Content-Type: application/json' );
        echo json_encode( $response );

        exit ( 0 );
    }

    /**
     * Override parent method to add 'wp_ajax_ab_' prefix
     * so current 'execute*' methods look nicer.
     */
    protected function registerWpActions( $prefix = '' )
    {
        parent::registerWpActions( 'wp_ajax_ab_' );
        parent::registerWpActions( 'wp_ajax_nopriv_ab_' );
    }
}
