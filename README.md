# pmpro_change_active_membership

==========Client Problem=========

This is  what seems we need to do for stripe subscription  We have to pick user active subscriptionID, make an API call to stripe and update the price for that customer subscription.  So that he is charged different pricing. Based on the article below it seems possible.  https://www.paidmembershipspro.com/modifying-payment-subscriptions-in-stripe/

The process right now is manual, but here is the code which updates it dynamically.
Goal is to modify pricing for active subscription without cancelling it.
==========Solution=========

This code allows users on wordpress websites using the plugin https://www.paidmembershipspro.com/
to change(update) pricing for active stripe subscriptions without canceling them.

This code does the following things:- 
# Set current level for user to inactive
# Insert New level for the user
# Makes a Stripe call to update the pricing plan in the existing active subscription.