<?php
$last_order = $wpdb->get_row( $wpdb->prepare( "
                      SELECT * FROM $wpdb->pmpro_membership_orders
                      WHERE user_id = %d
                      ORDER BY timestamp DESC LIMIT 1",
                      $user_id
                  ) );
if($last_order){
    //$msg = "<pre>".print_r($last_order)."</pre>";
    $subscription_transaction_id =  $last_order->subscription_transaction_id;
        if($secretkey!="" && $subscription_transaction_id){
            $stripe = new \Stripe\StripeClient($secretkey);
            if($subscription_transaction_id!=""){               
                try{
                    $subscriptions = $stripe->subscriptions->retrieve($subscription_transaction_id,[]);
                    $itemID = $subscriptions->items->data[0]->id;
                    if($itemID!=""){
                        $stripe->subscriptions->update(
                          $subscription_transaction_id,
                          [
                            'items' => [
                              [
                                'id' => $itemID,
                                'deleted' => true,
                              ],
                              ['price' => $stripe_price_id],// Your stripe planID, setup all plan somewhere on your website alogn with stripe
                            ],
                            'proration_behavior' => 'none',
                          ]
                        );


                        $last_order = $wpdb->get_row( $wpdb->prepare( "
                            SELECT * FROM $wpdb->pmpro_membership_orders
                            WHERE user_id = %d
                            ORDER BY timestamp DESC LIMIT 1",
                            $user_id
                        ) );

                        $membership_id = $desired_level_id;
                        $old_levels = pmpro_getMembershipLevelsForUser( $user_id );
                        //Get old level and deactivate it.
                        foreach ( $old_levels as $old_level ) {
                            $sql = "UPDATE $wpdb->pmpro_memberships_users SET `status`='inactive', `enddate`='" . esc_sql( current_time( 'mysql' ) ) . "' WHERE `id`=" . (int) $old_level->subscription_id;
                            if ( ! $wpdb->query( $sql ) ) {
                                $pmpro_error = __( 'Error interacting with database', 'paid-memberships-pro' ) . ': ' . ( $wpdb->last_error ? $wpdb->last_error : 'unavailable' );
                                return false;
                            }
                        }

                        $membership_level = new PMPro_Membership_Level();
                        $levelyourObject = $membership_level->get_membership_level( $membership_id );
                        $level = (array) $levelyourObject;
                       // Create a new membership record in pmpro_memberships_users table
                        if ( ! empty( $level ) ) {
                            // make sure the dates are in good formats
                            if ( is_array( $level ) ) {
                                $membership_subtotal  = $level['billing_amount'];
                                // Better support mySQL Strict Mode by passing  a proper enum value for cycle_period
                                if ( $level['cycle_period'] == '' ) {
                                    $level['cycle_period'] = 0; }

                                // clean up date formatting (string/not string)
                                $level['startdate'] = preg_replace( '/\'/', '', $level['startdate'] );
                                $level['enddate'] = preg_replace( '/\'/', '', $level['enddate'] );

                                $sql = $wpdb->prepare(
                                    "
                                        INSERT INTO {$wpdb->pmpro_memberships_users}
                                        (`user_id`, `status`, `membership_id`, `code_id`, `initial_payment`, `billing_amount`, `cycle_number`, `cycle_period`, `billing_limit`, `trial_amount`, `trial_limit`, `startdate`, `enddate`)
                                        VALUES
                                        ( %d, %s, %d, %d, %s, %s, %d, %s, %d, %s, %d, %s, %s )",
                                    $user_id, // integer
                                    'active', // integer
                                    $membership_id, // integer
                                    $level['code_id'], // integer
                                    $level['initial_payment'], // float (string)
                                    $level['billing_amount'], // float (string)
                                    $level['cycle_number'], // integer
                                    $level['cycle_period'], // string (enum)
                                    $level['billing_limit'], // integer
                                    $level['trial_amount'], // float (string)
                                    $level['trial_limit'], // integer
                                    $level['startdate'], // string (date)
                                    $level['enddate'] // string (date)
                                );
                                if ( false === $wpdb->query( $sql ) ) {
                                    //echo $pmpro_error = sprintf( __( 'Error interacting with database: %s', 'paid-memberships-pro' ), ( ! empty( $wpdb->last_error ) ? $wpdb->last_error : 'unavailable' ) );
                                    //return false;
                                }
                                // Create a new order for pmpro
                                $order = new MemberOrder();
                                $order->code = $order->getRandomCode();
                                $order->user_id = $user_id;
                                $order->membership_id = $membership_id;
                                $order->billing = new stdClass();
                                $order->billing->name = $last_order->billing_name;
                                $order->billing->street = $last_order->billing_street;
                                $order->billing->city = $last_order->billing_city;
                                $order->billing->state = $last_order->billing_state;
                                $order->billing->zip = $last_order->billing_zip;
                                $order->billing->country = $last_order->billing_country;
                                $order->billing->phone = $last_order->billing_phone;
                                $order->discount_code = '';
                                $order->subtotal = $membership_subtotal;
                                $order->tax = '';
                                $order->couponamount = '';
                                $order->total = $membership_subtotal;
                                $order->payment_type = '';
                                $order->cardtype = $last_order->cardtype;
                                $order->accountnumber = $last_order->cardtype;
                                $order->expirationmonth = $last_order->expirationmonth;
                                $order->expirationyear = $last_order->expirationyear;
                                $order->status = 'review';
                                $order->gateway = pmpro_getOption( 'gateway' );
                                $order->gateway_environment = pmpro_getOption( 'gateway_environment' );
                                $order->payment_transaction_id = $last_order->payment_transaction_id;
                                $order->subscription_transaction_id = $last_order->subscription_transaction_id;
                                $order->affiliate_id = '';
                                $order->affiliate_subid = '';
                                $order->notes = '';

                                if ( false !== $order->saveOrder() ) {
                                    $order_id = $order->id;
                                    //echo $pmpro_msg  = __( 'Order saved successfully.', 'paid-memberships-pro' );
                                    //echo $pmpro_msgt = 'success';
                                } else {
                                    //echo $pmpro_msg  = __( 'Error saving order.', 'paid-memberships-pro' );
                                    //echo $pmpro_msgt = 'error';
                                }
                            }
                        }

                    }      
                } catch (Exception $e) {
                  //print_r($e);
                }                                        
            }
        }
} 
