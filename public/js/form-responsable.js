/**
 * Montre la div si le checkbox est coché
 */

$(function() {
	$("#responsable-demenagement").change(
			function() {
				demenagement = $(this).is(":checked");
				$("#responsable-edit-blocAncien").toggle(demenagement);
				if (demenagement) {
					$("#responsable-ancienAdresseL1").val(
							$("#responsable-adresseL1").val());
					$("#responsable-ancienAdresseL2").val(
							$("#responsable-adresseL2").val());
					$("#responsable-ancienCodePostal").val(
							$("#responsable-codePostal").val());
					$("#responsable-ancienCommuneId").val(
							$("#responsable-communeId").val());
				} else {
					$("#responsable-ancienAdresseL1").val('');
					$("#responsable-ancienAdresseL2").val('');
					$("#responsable-ancienCodePostal").val('');
					$("#responsable-ancienCommuneId").removeAttr('selected');
				}
			});
});
