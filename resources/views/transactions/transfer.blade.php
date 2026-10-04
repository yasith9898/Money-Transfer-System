{{-- resources/views/transactions/transfer.blade.php --}}
@extends('layouts.app')

@section('title', 'Create Money Transfer')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-exchange-alt mr-2"></i>Create Money Transfer
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('transactions.transfer.store') }}" id="transferForm">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <!-- From Account -->
                                <div class="form-group">
                                    <label class="font-weight-bold">From Account *</label>
                                    <select name="from_account_id" id="from_account_id" class="form-control select2" required>
                                        <option value="">Select Sender Account</option>
                                        @foreach ($accounts as $account)
                                            <option value="{{ $account->id }}"
                                                    data-balances="{{ $account->getAllBalances()->toJson() }}"
                                                    data-user="{{ $account->user->name }}"
                                                    data-company="{{ $account->user->company_name }}">
                                                {{ $account->user->name }} - {{ $account->account_number }}
                                                @if (!$account->is_active)
                                                    (Inactive)
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Select the account that will send money</small>
                                </div>

                                <!-- To Account -->
                                <div class="form-group">
                                    <label class="font-weight-bold">To Account *</label>
                                    <select name="to_account_id" id="to_account_id" class="form-control select2" required>
                                        <option value="">Select Receiver Account</option>
                                        @foreach ($accounts as $account)
                                            <option value="{{ $account->id }}"
                                                    data-user="{{ $account->user->name }}"
                                                    data-company="{{ $account->user->company_name }}">
                                                {{ $account->user->name }} - {{ $account->account_number }}
                                                @if (!$account->is_active)
                                                    (Inactive)
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Select the account that will receive money</small>
                                </div>

                                <!-- Currency -->
                                <div class="form-group">
                                    <label class="font-weight-bold">Currency *</label>
                                    <select name="currency_id" id="currency_id" class="form-control" required>
                                        <option value="">Select Currency</option>
                                        @foreach ($currencies as $currency)
                                            <option value="{{ $currency->id }}" data-symbol="{{ $currency->symbol }}" data-code="{{ $currency->code }}"
                                                {{ $currency->code === 'EUR' ? 'selected' : '' }}>
                                                {{ $currency->code }} - {{ $currency->name }} ({{ $currency->symbol }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="exchange_section" style="display: none;">
                                    <h5 class="mt-4 mb-3 border-bottom pb-2 text-primary">
                                        <i class="fas fa-coins mr-2"></i>Exchange Calculation
                                    </h5>
                                    
                                    <div class="form-group">
                                        <label class="font-weight-bold">Exchange Rate</label>
                                        <input type="number" name="exchange_rate" id="exchange_rate" class="form-control" step="0.0001" min="0" placeholder="e.g. 1.15">
                                    </div>

                                    <div class="form-group">
                                        <label class="font-weight-bold">Divide or Multiply</label>
                                        <select name="exchange_action" id="exchange_action" class="form-control">
                                            <option value="multiply">Multiply (*)</option>
                                            <option value="divide">Divide (/)</option>
                                        </select>
                                    </div>

                                    <div class="form-group mb-4">
                                        <label class="font-weight-bold">Exchange Result</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light">Result</span>
                                            </div>
                                            <input type="text" name="exchange_result" id="exchange_result" class="form-control bg-light font-weight-bold" readonly value="0.00">
                                            <div class="input-group-append">
                                                <span class="input-group-text bg-light font-weight-bold">USD</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Amount -->
                                <div class="form-group">
                                    <label class="font-weight-bold">Amount *</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="currencySymbol">-</span>
                                        </div>
                                        <input type="number" name="amount" id="amount" class="form-control"
                                               step="0.01" min="0.01" required placeholder="0.00">
                                    </div>
                                    <small class="form-text text-muted">Transfer amount without commissions</small>
                                </div>



                                <!-- Company Commission -->
                                <div class="form-group mb-4">
                                    <label class="font-weight-bold">Company Commission</label>
                                    <div class="row">
                                        <div class="col-md-5 pr-md-1">
                                            <select id="company_commission_type" class="form-control">
                                                <option value="percent">Percentage (%)</option>
                                                <option value="fixed">Fixed Value</option>
                                            </select>
                                        </div>
                                        <div class="col-md-7 pl-md-1">
                                            <div class="input-group mt-2 mt-md-0">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="companyCommissionSymbol">%</span>
                                                </div>
                                                <input type="number" id="company_commission_input" class="form-control" step="0.01" placeholder="0.00">
                                            </div>
                                            <input type="hidden" name="company_commission" id="company_commission" value="0">
                                        </div>
                                    </div>
                                    <small class="form-text text-muted mt-1" id="companyCommissionCalcText" style="display: block;">
                                        Calculated Amount: <span id="companyCommissionSymbolCalc">-</span><span id="calculatedCompanyCommission">0.00</span>
                                    </small>
                                </div>

                                <!-- Turkey Commission -->
                                <div class="form-group mb-4">
                                    <label class="font-weight-bold">Turkey Commission</label>
                                    <div class="row">
                                        <div class="col-md-5 pr-md-1">
                                            <select id="turkey_commission_type" class="form-control">
                                                <option value="percent">Percentage (%)</option>
                                                <option value="fixed">Fixed Value</option>
                                            </select>
                                        </div>
                                        <div class="col-md-7 pl-md-1">
                                            <div class="input-group mt-2 mt-md-0">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="turkeyCommissionSymbol">%</span>
                                                </div>
                                                <input type="number" id="turkey_commission_input" class="form-control" step="0.01" placeholder="0.00">
                                            </div>
                                            <input type="hidden" name="turkey_commission" id="turkey_commission" value="0">
                                        </div>
                                    </div>
                                    <small class="form-text text-muted mt-1" id="turkeyCommissionCalcText" style="display: block;">
                                        Calculated Amount: <span id="turkeyCommissionSymbolCalc">-</span><span id="calculatedTurkeyCommission">0.00</span>
                                    </small>
                                </div>

                                <!-- Totals Display -->
                                <div class="form-group">

                                    <label class="font-weight-bold">First Total (Amount + Company Comm)</label>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light">Total 1</span>
                                        </div>
                                        <input type="text" id="total_first" class="form-control bg-light"
                                               readonly value="0.00">
                                    </div>

                                    <label class="font-weight-bold mt-2">Second Total (Amount + Turkey Comm)</label>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light">Total 2</span>
                                        </div>
                                        <input type="text" id="total_second" class="form-control bg-light"
                                               readonly value="0.00">
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="form-group">
                            <label class="font-weight-bold">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"
                                      placeholder="Enter transaction notes (optional)">{{ old('notes') }}</textarea>
                        </div>

                        <!-- Balance Information -->
                        <div class="alert alert-info" id="balanceInfo" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Current Balance:</strong>
                                    <span id="currentBalance">0.00</span>
                                </div>
                                <div>
                                    <strong>After Transfer:</strong>
                                    <span id="remainingBalance">0.00</span>
                                </div>
                            </div>
                        </div>

                        <!-- Insufficient Balance Warning -->
                        <div class="alert alert-warning" id="insufficientBalance" style="display: none;">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong>Warning: Insufficient Balance!</strong>
                            The selected account does not have enough balance for this transfer. The account balance will become negative.
                        </div>

                        <!-- Same Account Warning -->
                        <div class="alert alert-warning" id="sameAccountWarning" style="display: none;">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <strong>Warning!</strong>
                            Cannot transfer to the same account.
                        </div>

                        <!-- Form Actions -->
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                                <i class="fas fa-paper-plane mr-2"></i>Process Transfer
                            </button>
                            <a href="{{ route('transactions.index') }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times mr-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Transaction Summary Card -->
            <div class="card shadow mt-4" id="transactionSummary" style="display: none;">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Transaction Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>From:</strong> <span id="summaryFrom">-</span></p>
                            <p><strong>To:</strong> <span id="summaryTo">-</span></p>
                            <p><strong>Currency:</strong> <span id="summaryCurrency">-</span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Transfer Amount:</strong> <span id="summaryAmount">0.00</span></p>

                            <p><strong>Company Commission:</strong> <span id="summaryCompanyCommission">0.00</span></p>
                            <p><strong>Turkey Commission:</strong> <span id="summaryTurkeyCommission">0.00</span></p>

                            <p><strong>First Total:</strong> <span id="summaryFirstTotal">0.00</span></p>
                            <p><strong>Second Total:</strong> <span id="summarySecondTotal">0.00</span></p>


                            <div id="summaryExchangeResultRow" style="display: none; margin-top: 10px; padding-top: 10px; border-top: 1px dashed #ccc;">
                                <p class="text-primary mb-0"><strong>Exchange Result:</strong> <span id="summaryExchangeResult">0.00</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<style>
.select2-container--default .select2-selection--single {
    height: calc(1.5em + 0.75rem + 2px);
    padding: 0.375rem 0.75rem;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: calc(1.5em + 0.75rem + 2px);
}
.form-group {
    margin-bottom: 1.5rem;
}
.alert {
    border-left: 4px solid;
}
#transactionSummary {
    border-left: 4px solid #007bff;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        placeholder: "Select an account",
        allowClear: true
    });

    let currentBalance = 0;
    let currencySymbol = '';

    function updateCommissionSymbols() {
        if ($('#company_commission_type').val() === 'fixed') {
            $('#companyCommissionSymbol').text(currencySymbol);
            $('#companyCommissionCalcText').hide();
        } else {
            $('#companyCommissionSymbol').text('%');
            $('#companyCommissionSymbolCalc').text(currencySymbol);
            $('#companyCommissionCalcText').show();
        }

        if ($('#turkey_commission_type').val() === 'fixed') {
            $('#turkeyCommissionSymbol').text(currencySymbol);
            $('#turkeyCommissionCalcText').hide();
        } else {
            $('#turkeyCommissionSymbol').text('%');
            $('#turkeyCommissionSymbolCalc').text(currencySymbol);
            $('#turkeyCommissionCalcText').show();
        }
    }

    $('#company_commission_type, #turkey_commission_type').change(function() {
        updateCommissionSymbols();
        updateTotalAmount();
        checkBalance();
        updateTransactionSummary();
    });

    // Update currency symbol when currency changes
    $('#currency_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        currencySymbol = selectedOption.data('symbol') || '';
        const currencyCode = selectedOption.data('code') || '';

        $('#currencySymbol').text(currencySymbol);

        if (currencyCode && currencyCode.toUpperCase() !== 'USD') {
            $('#exchange_section').slideDown();
        } else {
            $('#exchange_section').slideUp();
            $('#exchange_rate').val('');
            $('#exchange_result').val('0.00');
        }
        updateCommissionSymbols();

        updateTotalAmount();
        checkBalance();
        updateTransactionSummary();
    });

    // Update total amount when any amount field changes
    $('#amount, #company_commission_input, #turkey_commission_input').on('input', function() {
        updateTotalAmount();
        checkBalance();
        updateTransactionSummary();
    });

    // Check balance when from account or currency changes
    $('#from_account_id, #currency_id').change(function() {
        checkBalance();
        updateTransactionSummary();
    });

    // Check for same account selection
    $('#from_account_id, #to_account_id').change(function() {
        const fromAccount = $('#from_account_id').val();
        const toAccount = $('#to_account_id').val();

        if (fromAccount && toAccount && fromAccount === toAccount) {
            $('#sameAccountWarning').show();
            $('#submitBtn').prop('disabled', true);
        } else {
            $('#sameAccountWarning').hide();
            updateSubmitButton();
        }

        updateTransactionSummary();
    });

    // Calculate total amounts
    function updateTotalAmount() {
        const amount = parseFloat($('#amount').val()) || 0;
        
        const compInput = parseFloat($('#company_commission_input').val()) || 0;
        const turkInput = parseFloat($('#turkey_commission_input').val()) || 0;

        let companyCommission = 0;
        if ($('#company_commission_type').val() === 'fixed') {
            companyCommission = compInput;
        } else {
            companyCommission = (amount * compInput) / 100;
        }

        let turkeyCommission = 0;
        if ($('#turkey_commission_type').val() === 'fixed') {
            turkeyCommission = turkInput;
        } else {
            turkeyCommission = (amount * turkInput) / 100;
        }

        $('#company_commission').val(companyCommission.toFixed(2));
        $('#calculatedCompanyCommission').text(companyCommission.toFixed(2));

        $('#turkey_commission').val(turkeyCommission.toFixed(2));
        $('#calculatedTurkeyCommission').text(turkeyCommission.toFixed(2));

        const firstTotal = amount + companyCommission;
        const secondTotal = amount + turkeyCommission;

        // Display totals
        $('#total_first').val(firstTotal.toFixed(2));
        $('#total_second').val(secondTotal.toFixed(2));

        // Calculate Exchange Result
        calculateExchangeResult(amount, companyCommission);

        // Use First Total (Amount + Company Comm) for deduction check as per existing logic
        return firstTotal;
    }

    function calculateExchangeResult(amount, companyCommission) {
        if ($('#exchange_section').is(':visible')) {
            const exchangeRate = parseFloat($('#exchange_rate').val()) || 0;
            const action = $('#exchange_action').val();
            let result = 0;

            if (exchangeRate > 0) {
                if (action === 'multiply') {
                    result = (amount * exchangeRate) + companyCommission;
                } else if (action === 'divide') {
                    result = (amount / exchangeRate) + companyCommission;
                }
            }

            // Display result
            $('#exchange_result').val(result.toFixed(2));
            $('#summaryExchangeResult').text('USD ' + result.toFixed(2));
        } else {
            $('#summaryExchangeResultRow').hide();
        }
    }

    $('#exchange_rate, #exchange_action').on('input change', function() {
        updateTotalAmount();
        updateTransactionSummary();
    });

    // Check account balance
    function checkBalance() {
        const fromAccountId = $('#from_account_id').val();
        const currencyId = $('#currency_id').val();
        const totalAmount = updateTotalAmount();

        if (fromAccountId && currencyId) {
            const fromAccount = $('#from_account_id option:selected');
            const balances = fromAccount.data('balances');

            if (balances) {
                const balanceObj = balances.find(b => b.currency_id == currencyId);
                currentBalance = balanceObj ? parseFloat(balanceObj.balance) : 0;

                // Show balance info
                $('#balanceInfo').show();
                $('#currentBalance').text(currencySymbol + currentBalance.toFixed(2));
                const afterTransfer = currentBalance - totalAmount;
                $('#remainingBalance').text(currencySymbol + afterTransfer.toFixed(2));

                // Change color if negative
                if (afterTransfer < 0) {
                    $('#remainingBalance').addClass('text-danger');
                    $('#insufficientBalance').show();
                } else {
                    $('#remainingBalance').removeClass('text-danger');
                    $('#insufficientBalance').hide();
                }
                
                updateSubmitButton();
            }
        } else {
            $('#balanceInfo').hide();
            $('#insufficientBalance').hide();
            $('#submitBtn').prop('disabled', true);
        }
    }

    // Update submit button state
    function updateSubmitButton() {
        const fromAccount = $('#from_account_id').val();
        const toAccount = $('#to_account_id').val();
        const currency = $('#currency_id').val();
        const amount = parseFloat($('#amount').val()) || 0;

        const isValid = fromAccount && toAccount && currency && amount > 0 &&
                       fromAccount !== toAccount;

        $('#submitBtn').prop('disabled', !isValid);
    }

    // Update transaction summary
    function updateTransactionSummary() {
        const fromAccount = $('#from_account_id option:selected');
        const toAccount = $('#to_account_id option:selected');
        const currency = $('#currency_id option:selected');
        const amount = parseFloat($('#amount').val()) || 0;

        const companyCommission = parseFloat($('#company_commission').val()) || 0;
        const turkeyCommission = parseFloat($('#turkey_commission').val()) || 0;

        if (fromAccount.val() && toAccount.val() && currency.val()) {
            $('#transactionSummary').show();
            $('#summaryFrom').text(fromAccount.data('user') + ' - ' + fromAccount.data('company'));
            $('#summaryTo').text(toAccount.data('user') + ' - ' + toAccount.data('company'));
            $('#summaryCurrency').text(currency.text());
            $('#summaryAmount').text(currencySymbol + amount.toFixed(2));

            $('#summaryCompanyCommission').text(currencySymbol + companyCommission.toFixed(2));
            $('#summaryTurkeyCommission').text(currencySymbol + turkeyCommission.toFixed(2));

            $('#summaryFirstTotal').text(currencySymbol + (amount + companyCommission).toFixed(2));
            $('#summarySecondTotal').text(currencySymbol + (amount + turkeyCommission).toFixed(2));

            if ($('#exchange_section').is(':visible') && parseFloat($('#exchange_rate').val()) > 0) {
                $('#summaryExchangeResultRow').show();
            } else {
                $('#summaryExchangeResultRow').hide();
            }
        } else {
            $('#transactionSummary').hide();
        }
    }

    // Form submission confirmation
    $('#transferForm').submit(function(e) {
        const total = updateTotalAmount();
        if (!confirm(`Are you sure you want to process this transfer?\n\nTotal Amount: ${currencySymbol}${total.toFixed(2)}`)) {
            e.preventDefault();
        }
    });

    // Initialize
    $('#currency_id').trigger('change');
    updateTotalAmount();
});
</script>
@endpush
