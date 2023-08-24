# pmpro_change_active_membership
This is  what seems we need to do for stripe subscription  We have to pick user active subscriptionID, make an API call to stripe and update the price for that customer.  So that he is charged different pricing. Based on the article below it seems possible.  https://www.paidmembershipspro.com/modifying-payment-subscriptions-in-stripe/

The process right now is manual, but here is the code which updates it dynamically. 
