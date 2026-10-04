# TODO: Fix Balance Update Issue After Transactions

## Issue Identified
- The `voidTransaction` method in `TransactionController.php` was not updating account balances when voiding transactions.
- This caused the Balances column in the accounts table to not reflect correct values after voiding.

## Fix Applied
- [x] Updated `voidTransaction` method to properly reverse balance updates:
  - Add back the total amount (transaction + commission) to the sender account
  - Deduct the transaction amount from the receiver account
  - Deduct any company commission from the main wallet
- [x] Ensured the fix is applied within a database transaction for consistency

## Follow-up Steps
- [ ] Test the void transaction functionality to ensure balances update correctly
- [ ] Verify that the accounts index page now shows accurate balance values after voiding transactions
- [ ] Check for any other transaction-related methods that might have similar issues (e.g., other reversal or adjustment methods)

## Files Modified
- `app/Http/Controllers/TransactionController.php` - Fixed `voidTransaction` method to update balances
