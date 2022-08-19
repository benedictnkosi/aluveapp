$(document).ready(function () {
    $(".deal_dropdown").change(function (event) {
        event.stopImmediatePropagation();
        recalculateAll();
    });

    calculateProfitAndLoss();
    $('#property_link').html('<a href="'+ sessionStorage.getItem('propertyUrl') + '" target="_blank">View Property</a>');
});

function recalculateAll(){
    calculateProfitAndLoss();
}

function calculateDeposit(){
    let percentage = $('#deposit_percent').find(':selected').val();
    let offerPercentage = $('#offer_percent').find(':selected').val();
    let sellingPrice = parseInt($('#selling_price').html()) * offerPercentage;
    let deposit = sellingPrice * percentage;
    $('#purchase_price').html(sellingPrice.toLocaleString('en-US'));
    $('#deposit').html(deposit.toLocaleString('en-US'));

    let bond = sellingPrice - deposit;
    $('#bond').html(bond.toLocaleString('en-US'));
    return deposit;
}

function calculateRenovations(){
    let percentage = $('#renovations_percent').find(':selected').val();
    let avgSellingPrice = parseInt($('#avg_price').html()) * $('#selling_price_percent').find(':selected').val();
    let renovations = avgSellingPrice * percentage;
    $('#renovations').html(renovations.toLocaleString('en-US'));
    return parseInt(renovations);
}

function calculateUtilities(){
    let avgSellingPrice = parseInt($('#avg_price').html()) * $('#selling_price_percent').find(':selected').val();
    let utilities = avgSellingPrice * 0.002;
    $('#utilities_cost').html(utilities.toLocaleString('en-US'));
    return parseInt(utilities);
}

function calculateBondCost(){
    let sellingPrice = parseInt($('#selling_price').html()) * parseFloat($('#offer_percent').find(':selected').val());
    let bankInitiationFee = 6000;
    let deedsOfficeFees = 1500;
    let postFees = 2200;
    let bondRegistration = parseInt(sellingPrice) * 0.03;
    let bond_cost = bondRegistration  + bankInitiationFee + deedsOfficeFees + postFees;
    $('#bond_cost').html(bond_cost.toLocaleString('en-US'));
    return parseInt(bond_cost);
}

function calculateTransferCost(){
    let deedsOfficeFees = 1500;
    let postFees = 2200;
    let sellingPrice = parseInt($('#selling_price').html()) * parseFloat($('#offer_percent').find(':selected').val());
    let transferWithoutDuty = parseInt(sellingPrice) * 0.026;
    let transferDuty = 0;
    if(parseInt(sellingPrice) > 1000000 && parseInt(sellingPrice) < 1375001){
        transferDuty = (sellingPrice - 1000000) * 0.03;
    }else if(parseInt(sellingPrice) > 1375000 && parseInt(sellingPrice) < 1925001){
        transferDuty = 11250 + (sellingPrice - 1375000) * 0.06;
    }else if(parseInt(sellingPrice) > 1925000 && parseInt(sellingPrice) < 2475001){
        transferDuty = 44250 + (sellingPrice - 1925000) * 0.11;
    }
    let transferCost = transferWithoutDuty + transferDuty + deedsOfficeFees + postFees;
    $('#transfer_cost').html(transferCost.toLocaleString('en-US'));
    return parseInt(transferCost);
}

function calculateBondPayments(){
    let months_holding_property = parseInt($('#months_holding_property').find(':selected').val());
    let sellingPrice = parseInt($('#selling_price').html()) * parseFloat($('#offer_percent').find(':selected').val());
    let percentage = $('#deposit_percent').find(':selected').val();
    let deposit = sellingPrice * percentage;
    let bond = sellingPrice - deposit;

    let monthlyBondPayment = bond * 0.0089;
    let totalBondPayments = monthlyBondPayment * months_holding_property;
    $('#bond_payments_cost').html(  totalBondPayments.toLocaleString('en-US'));
    return parseInt(totalBondPayments);
}

function calculateMunicipalRates(){
    let months_holding_property = parseInt($('#months_holding_property').find(':selected').val());
    let sellingPrice = parseInt($('#selling_price').html()) * parseFloat($('#offer_percent').find(':selected').val());
    let rate = ((sellingPrice - 200000) * 0.006161)/12;
    let totalRates = rate * months_holding_property;
    $('#rates_cost').html(  totalRates.toLocaleString('en-US'));
    return parseInt(totalRates);
}

function calculateCommission(){
    let avgSellingPrice = parseInt($('#avg_price').html()) * $('#selling_price_percent').find(':selected').val();
    let commission = ((avgSellingPrice - 200000) * 0.03);
    $('#commission_cost').html(  commission.toLocaleString('en-US'));
    return commission;
}

function calculateTotalBuyingCosts(){
    let inspectionCost = parseInt($('#inspection_cost').html());
    let bondCosts = calculateBondCost();
    let transferCosts = calculateTransferCost();
    let totalHoldingCosts = inspectionCost + bondCosts + transferCosts
    $('#total_buying_costs').html( totalHoldingCosts.toLocaleString('en-US'));
    return parseInt(totalHoldingCosts);
}


function calculateTotalHoldingCosts(){
    let bondPayments = calculateBondPayments();
    let municipalRates = calculateMunicipalRates();
    let Utilities = calculateUtilities();
    let totalHoldingCosts = bondPayments + municipalRates + Utilities;
    $('#total_holding_costs').html( totalHoldingCosts.toLocaleString('en-US'));
    return parseInt(totalHoldingCosts);
}


function calculateProfitAndLoss(){
    let months_holding_property = parseInt($('#months_holding_property').find(':selected').val());
    let sellingPrice = parseInt($('#selling_price').html()) * parseFloat($('#offer_percent').find(':selected').val());
    let deposit = calculateDeposit();
    let avgSellingPrice = parseInt($('#avg_price').html()) * $('#selling_price_percent').find(':selected').val();
    let renovations = calculateRenovations();
    let buyingCosts = calculateTotalBuyingCosts();
    let holdingCosts = calculateTotalHoldingCosts();
    let sellingCosts = calculateCommission();
    let depositBuyingCosts = deposit + buyingCosts;
    let totalInvested = deposit + renovations + buyingCosts + holdingCosts;
    $('#selling_price_2').html( 'R' + avgSellingPrice.toLocaleString('en-US'));
    $('#total_invested').html( '-R' + totalInvested.toLocaleString('en-US'));
    $('#selling_cost').html( '-R' + sellingCosts.toLocaleString('en-US'));
    $('#deposit_buying_costs').html( 'R' + depositBuyingCosts.toLocaleString('en-US'));

    let bond = sellingPrice - deposit;
    $('#bond_payoff').html('-R' + bond.toLocaleString('en-US'));
    let profit = avgSellingPrice - totalInvested - sellingCosts - bond;
    $('.profit').html('R' + profit.toLocaleString('en-US'));

    let monthsToBuy = 3;
    let totalMonthsInvested =monthsToBuy +months_holding_property;
    let roiPerYear = (profit/totalInvested) * 100;

    $('.roi').html(roiPerYear.toFixed() + '%');
}