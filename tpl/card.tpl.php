<!-- Un début de <div> existe de par la fonction dol_fiche_head() -->
	<input type="hidden" name="newtoken" value="[var._SESSION.newtoken]" />
	<input type="hidden" name="action" value="[view.action]" />
	<input type="hidden" name="step" value="[view.step]" />
	<textarea name="TDataCompressed" style="display:none;">[TDataCompressed]</textarea>

	[TError;block=begin]
	<div class="warning error">
		<p>[langs.transnoentities([TError.val;noerr]);strconv=no]</p>
	</div>
	[TError;block=end]

	<div class="fichecenter">
		<table width="100%" class="border tableforfield">
			<tbody>
				<tr class="file">
					<td width="15%"><span class="fieldrequired">[langs.transnoentities(FileToImport)]</span></td>
					<td width="45%">[view.showInputFile;strconv=no]</td>
					<td width="15%"><span class="">[langs.transnoentities(NbIgnore)]</span></td>
					<td width="25%">[view.showNbIgnore;strconv=no]</td>
				</tr>
				<tr class="payment_date">
					<td width="15%"><span class="fieldrequired">[langs.transnoentities(PaymentDate)]</span></td>
					<td width="45%">[view.showInputPaymentDate;strconv=no]</td>
					<td width="15%"><span class="">[langs.transnoentities(Delimiter)]</span></td>
					<td width="25%">[view.showDelimiter;strconv=no]</td>
				</tr>
				<tr class="fk_c_paiement">
					<td width="15%"><span class="fieldrequired">[langs.transnoentities(PaymentMode)]</span></td>
					<td width="45%">[view.showInputPaymentMode;strconv=no]</td>
					<td width="15%"><span class="">[langs.transnoentities(Enclosure)]</span></td>
					<td width="25%">[view.showEnclosure;strconv=no]</td>
				</tr>
				<tr class="fk_bank_account">
					<td width="15%"><span class="fieldrequired">[langs.transnoentities(AccountToCredit)]</span></td>
					<td width="45%">[view.showInputAccountToCredit;strconv=no]</td>
					<td width="15%"><span class="">[langs.transnoentities(ClosePaidInvoices)]</span></td>
					<td width="25%">[view.showClosePaidInvoices;strconv=no]</td>
				</tr>
				<tr class="fk_Specialtreatment">
					<td width="15%"><span class="">[langs.transnoentities(AvoidAlreadyPaidInvoice)]</span></td>
					<td width="45%">[view.showAvoidAlreadyPaidInvoice;strconv=no]</td>
					<td width="15%"><span class="">[langs.transnoentities(DoNotDoublePaiement)]</span></td>
					<td width="25%">[view.showDoNotImpotDoublePayment;strconv=no]</td>
				</tr>
			</tbody>
		</table>
	</div>

	[onshow;block=begin;when [TData.#]+-0]
	<br />
	<div class="underbanner clearboth"></div>
	<table width="100%" class="border">
		<tbody>
			<tr class="liste_titre">
				<th colspan="[view.colspan;noerr]">[langs.transnoentities(ImportReglementDataParsed)]</th>
			</tr>
			<tr class="liste_titre">
				[onshow;block=begin;when [view.step]==2]
					<th><input type="checkbox" title="[langs.transnoentities(ImportReglementCheckboxToImport)]" onclick="$('.TLineIndex').prop('checked', $(this).is(':checked'));" /></th>
				[onshow;block=end]

				<th class="[TFieldOrder.field;block=th]">
					[onshow;block=begin;when [conf.global.IMPORTREGLEMENT_REORDER_ALLOWED;noerr]==1]
						<input type="hidden" name="TField[]" value="[TFieldOrder.field]" />
					[onshow;block=end]
					[TFieldOrder.label;strconv=no]
				</th>
			</tr>

			<tr class="impair">
				[onshow;block=begin;when [view.step]==2]
					<td><input class="TLineIndex" type="checkbox" name="TLineIndex[]" value="[TData.$]"/></td>
				[onshow;block=end]
				<!-- [TData.$;block=tr;sub1] -->
				<td field="[TData_sub1.$]">
					[TData_sub1.val;block=td;fieldname=[TData_sub1.$];onformat=getValue;strconv=no]
					<input type="hidden" name="TData[[TData.$]][]" value="[TData_sub1.val;onformat=getSanitizedValue]" />
				</td>
			</tr>
			<tr class="pair">
				[onshow;block=begin;when [view.step]==2]
					<td><input class="TLineIndex" type="checkbox" name="TLineIndex[]" value="[TData.$]"/></td>
				[onshow;block=end]
				<!-- [TData.$;block=tr;sub1] -->
				<td field="[TData_sub1.$]">
					[TData_sub1.val;block=td;fieldname=[TData_sub1.$];onformat=getValue;strconv=no]
					<input type="hidden" name="TData[[TData.$]][]" value="[TData_sub1.val;onformat=getSanitizedValue]" />
				</td>
			</tr>

		</tbody>
	</table>
	[onshow;block=end]


</div> <!-- Fin div de la fonction dol_fiche_head() -->


<div class="center">
	<input type="submit" class="button" value="[langs.transnoentities(SubmitLabel_[view.action])]" />
</div>
