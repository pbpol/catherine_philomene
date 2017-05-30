/**
* E-Transactions PrestaShop Module
*
* Feel free to contact E-Transactions at support@e-transactions.fr for any
* question.
*
* LICENSE: This source file is subject to the version 3.0 of the Open
* Software License (OSL-3.0) that is available through the world-wide-web
* at the following URI: http://opensource.org/licenses/OSL-3.0. If
* you did not receive a copy of the OSL-3.0 license and are unable 
* to obtain it through the web, please send a note to
* support@e-transactions.fr so we can mail you a copy immediately.
*
*  @category  Module / payments_gateways
*  @version   2.2.2
*  @author    E-Transactions <support@e-transactions.fr>
*  @copyright 2012-2016 E-Transactions
*  @license   http://opensource.org/licenses/OSL-3.0
*  @link      http://www.e-transactions.fr/
*/

$(document).ready(function()
{
	if (refundAvailable == 1) {
		etransCreateCreditSlip();

		$('#generateDiscount').on('click', function() {
			etransToggleCreditSlip();
		});
		$('#generateCreditSlip').on('click', function() {
			etransToggleCreditSlip();
		});
	}

	/**
	 * Intercept Ajax calls for page reload on product modification or deletion
	 */
	$(document).ajaxSuccess(function(event, xhr, settings, data) {
		if (typeof data !== 'undefined' && data !== null) {
			if (typeof data.documents_html !== 'undefined' && data.documents_html !== null) {
				window.location.reload(true);
			}
		}
	});
});

/**
 * Create Checkbox for refund
 */
function etransCreateCreditSlip()
{
	html = 
		'<p class="checkbox" id="etransRefundSpan" style="display: none;">'+
			'<label for="etransRefund">'+
				'<input type="checkbox" id="etransRefund" name="etransRefund" />'+
				refundCheckboxText +
			'</label>'+
		'</p>';

	$('#spanShippingBack').after(html);

}

/**
 * Handle validation of refund checkbox
 * => only available if generateCreditSlip checked and generateDiscount unchecked
 */
function etransToggleCreditSlip()
{
	generateDiscount = $('#generateDiscount').attr("checked");
	generateCreditSlip = $('#generateCreditSlip').attr("checked");
	if (generateDiscount != 'checked' && generateCreditSlip == 'checked')
	{
		$('#etransRefundSpan').css('display', 'block');
	}
	else {
		$('#etransRefundSpan input[type=checkbox]').attr("checked", false);
		// $('#etransRefundSpan').css('display', 'none');
	}
}
