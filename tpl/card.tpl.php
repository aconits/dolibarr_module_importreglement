<!-- Un début de <div> existe de par la fonction dol_fiche_head() -->
	<input type="hidden" name="action" value="[view.action]" />
	<input type="hidden" name="step" value="[view.step]" />
	<table width="100%" class="border">
		<tbody>
			<tr class="file">
				<td width="25%"><span class="fieldrequired">[langs.transnoentities(FileToImport)]</span></td>
				<td>[view.showInputFile;strconv=no;magnet=tr]</td>
			</tr>
			<tr class="payment_date">
				<td width="25%"><span class="fieldrequired">[langs.transnoentities(PaymentDate)]</span></td>
				<td>[view.showInputPaymentDate;strconv=no;magnet=tr]</td>
			</tr>
			<tr class="fk_c_paiement">
				<td width="25%"><span class="fieldrequired">[langs.transnoentities(PaymentMode)]</span></td>
				<td>[view.showInputPaymentMode;strconv=no;magnet=tr]</td>
			</tr>
			<tr class="fk_bank_account">
				<td width="25%"><span class="fieldrequired">[langs.transnoentities(AccountToCredit)]</span></td>
				<td>[view.showInputAccountToCredit;strconv=no;magnet=tr]</td>
			</tr>
		</tbody>
	</table>

	<table width="100%" class="border">
		<tbody>
			<tr class="impair">
				<!-- [TData.$;block=tr;sub1] -->
				<td field="[TData_sub1.$]">[TData_sub1.val;block=td; strconv=no]</td>
			</tr>
			<tr class="pair">
				<!-- [TData.$;block=tr;sub1] -->
				<td field="[TData_sub1.$]">[TData_sub1.val;block=td; strconv=no]</td>
			</tr>
		</tbody>
	</table>

</div> <!-- Fin div de la fonction dol_fiche_head() -->


<div class="center">
	<input class="button" value="[langs.transnoentities(ParseFile)]" type="submit">
</div>
