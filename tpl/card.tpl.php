<!-- Un début de <div> existe de par la fonction dol_fiche_head() -->
	<input type="hidden" name="action" value="[view.action]" />
	<input type="hidden" name="step" value="[view.step]" />
	<table width="100%" class="border">
		<tbody>
			<tr class="ref">
				<td width="25%"><span class="fieldrequired">[langs.transnoentities(FileToImport)]</span></td>
				<td>[view.showInputFile;strconv=no]</td>
			</tr>
			<tr class="ref">
				<td width="25%"><span class="fieldrequired">[langs.transnoentities(PaymentDate)]</span></td>
				<td>[view.showInputPaymentDate;strconv=no]</td>
			</tr>
			<tr class="ref">
				<td width="25%"><span class="fieldrequired">[langs.transnoentities(PaymentMode)]</span></td>
				<td>[view.showInputPaymentMode;strconv=no]</td>
			</tr>
			<tr class="ref">
				<td width="25%"><span class="fieldrequired">[langs.transnoentities(AccountToCredit)]</span></td>
				<td>[view.showInputAccountToCredit;strconv=no]</td>
			</tr>
		</tbody>
	</table>

</div> <!-- Fin div de la fonction dol_fiche_head() -->


<div class="center">
	<input class="button" value="[langs.transnoentities(ParseFile)]" type="submit">
</div>
