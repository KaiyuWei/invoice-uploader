This is a Laravel-based API that allows uploading a sales invoice with
one or more invoice lines, and then "forwards" this invoice to Exact Online.

## Simplified 
### Handling the failure of sending to ExactOnline

There are different ways to handle this situation:

1. Retry the send invoice operation in limited times, say at most 3 times. If it still fails, we should send an email to the user with the invoice details. This method can be used when we want to use the ExactOnline cloud service to send the invoice data with our customer.

2. Retry the send invoice operation in limited times. If it still fails, we should roll back the invoice data stored in the app database;

    OR, we can first create the Eloquent object of invoice and invoice lines, only when the response is successful,we can then store the invoice and invoice lines in the database.

    This method can be used when the synchronization is strictly required, e.g. when making the backup of the invoice data.

3. We can also just log the invoice id when it still fails after the max try times. Later the list of invoice ids that failed to send to ExactOnline can be processed by a cron job regularly. This method fit for the case that the synchronization is not strictly required, and it is also not urgent to do.

Given that we're just simulating the implementation, we choose the simplest method, i.e. just log the invoice id.