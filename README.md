# pmpro_change_active_membership

==========Client Problem=========

This is  what seems we need to do for the stripe subscription  We have to pick the user's active subscriptionID, make an API call to stripe, and update the price for that customer subscription.  So that he is charged different pricing. Based on the article below it seems possible.  https://www.paidmembershipspro.com/modifying-payment-subscriptions-in-stripe/

The process right now is manual, but here is the code which updates it dynamically.
The goal is to modify pricing for an active subscription without canceling it.
==========Solution=========

This code allows users on WordPress websites to use the plugin https://www.paidmembershipspro.com/
to change(update) pricing for active stripe subscriptions without canceling them.

# To automate this feature for paidmembershipspro plugin, the below code does the following things:- 
Set the current level for the user to inactive
Insert a New level for the user
Make a Stripe call to update the pricing plan in the existing active subscription.
