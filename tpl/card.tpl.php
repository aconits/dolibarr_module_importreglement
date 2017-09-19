<!-- Un début de <div> existe de par la fonction dol_fiche_head() -->
	<input type="hidden" name="action" value="[view.action]" />
	<input type="hidden" name="step" value="[view.step]" />
	<table width="100%" class="border">
		<tbody>
			<tr class="file">
				<td width="15%"><span class="fieldrequired">[langs.transnoentities(FileToImport)]</span></td>
				<td>[view.showInputFile;strconv=no]</td>
				<td width="15%"><span class="fieldrequired">[langs.transnoentities(NbIgnore)]</span></td>
				<td>[view.showNbIgnore;strconv=no]</td>
			</tr>
			<tr class="payment_date">
				<td width="15%"><span class="fieldrequired">[langs.transnoentities(PaymentDate)]</span></td>
				<td>[view.showInputPaymentDate;strconv=no]</td>
				<td width="15%"><span class="fieldrequired">[langs.transnoentities(Delimiter)]</span></td>
				<td>[view.showDelimiter;strconv=no]</td>
			</tr>
			<tr class="fk_c_paiement">
				<td width="15%"><span class="fieldrequired">[langs.transnoentities(PaymentMode)]</span></td>
				<td>[view.showInputPaymentMode;strconv=no]</td>
				<td width="15%"><span class="fieldrequired">[langs.transnoentities(Enclosure)]</span></td>
				<td>[view.showEnclosure;strconv=no]</td>
			</tr>
			<tr class="fk_bank_account">
				<td width="15%"><span class="fieldrequired">[langs.transnoentities(AccountToCredit)]</span></td>
				<td>[view.showInputAccountToCredit;strconv=no]</td>
			</tr>
		</tbody>
	</table>

	
	[onshow;block=begin;when [TData.#]+-0]
	<hr />
	<table width="100%" class="border">
		<tbody>
			<tr class="liste_titre">
				<th colspan="[view.colspan;noerr]">[langs.transnoentities(ImportPaymentDataParsed)]</th>
			</tr>
			<tr class="liste_titre">
				<th><input type="checkbox" title="[langs.transnoentities(ToImport)]" onclick="$('.TLineIndex').attr('checked', $(this).is(':checked'));" /></th>
			</tr>
			<tr class="impair">
				<td><input class="TLineIndex" type="checkbox" name="TLineIndex[]" value="[TData.$]"/></td>
				<!-- [TData.$;block=tr;sub1] -->
				<td field="[TData_sub1.$]">[TData_sub1.val;block=td; strconv=no]</td>
			</tr>
			<tr class="pair">
				<td><input class="TLineIndex" type="checkbox" name="TLineIndex[]" value="[TData.$]"/></td>
				<!-- [TData.$;block=tr;sub1] -->
				<td field="[TData_sub1.$]">[TData_sub1.val;block=td; strconv=no]</td>
			</tr>
		</tbody>
	</table>
	[onshow;block=end]
	

</div> <!-- Fin div de la fonction dol_fiche_head() -->


<div class="center">
	<input class="button" value="[langs.transnoentities(ParseFile)]" type="submit">
</div>
