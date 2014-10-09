/**
 * Montre la div si le checkbox est coché
 */

$(function() {
	$("#responsable-demenagement").change(
			function() {
				demenagement = $(this).is(":checked");
				$("#responsable-edit-blocAncien").toggle(demenagement);
				if (demenagement) {
					$("#responsable-ancienAdressL1").val(
							$("#responsable-adressL1").val());
					$("#responsable-ancienAdressL2").val(
							$("#responsable-adressL2").val());
					$("#responsable-ancienCodePostal").val(
							$("#responsable-codePostal").val());
					$("#responsable-ancienCommuneId").val(
							$("#responsable-communeId").val());
				} else {
					$("#responsable-ancienAdressL1").val('');
					$("#responsable-ancienAdressL2").val('');
					$("#responsable-ancienCodePostal").val('');
					$("#responsable-ancienCommuneId").removeAttr('selected');
				}
			});
});
