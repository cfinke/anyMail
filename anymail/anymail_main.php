<?php

include("globals.php");

$_SESSION["anymail"]["compose"]["attached_files"] = array();

$output = '
	<!-- Set up a full page form that will be used for any page-changing actions. -->
	
	<table cellpadding="0" cellspacing="0" style="margin-left: 10px; margin-top: 10px; width: 95%;">
		<tr>
			<td id="main_frame" colspan="2">
				<table cellspacing="0" cellpadding="0">
					<tr>
						<td style="width: 75%;">
							<form name="global_form" id="inner_frame">
							</form>
						</td>
						<td style="width: 25%; vertical-align: top; padding-left: 20px;">
							<div id="classification_area">
								<div id="address_book">
								</div>
								<table cellpadding="0" cellspacing="0">
									<tr>
										<td style="width: 20px; vertical-align: bottom;"><img src="images/top-left-round.gif" /></td>
										<td style="width: 99%; font-weight: bold; color: #ffffff; background-color: #B1B1B1; text-align: center; vertical-align: middle;">Labels</td>
										<td style="width: 20px; vertical-align: bottom;"><img src="images/top-right-round.gif" /></td>
									</tr>
									<tr>
										<td colspan="3" id="label_frame" style="width: 100%; background-color: #ffffff; vertical-align: top;">
										</td>
									</tr>
									<tr>
										<td colspan="3" style="height: 29px; text-align: center; font-weight: bold; color: #ffffff; background-color: #B1B1B1; vertical-align: middle;">Contacts</td>
									</tr>
									<tr>
										<td colspan="3" id="contact_frame" style="width: 100%; background-color: #ffffff; vertical-align: top;">
										</td>
									</tr>
									<tr>
										<td colspan="3" style="height: 29px; text-align: center; font-weight: bold; color: #ffffff; background-color: #B1B1B1; vertical-align: middle;">Thread Arc</td>
									</tr>
									<tr>
										<td colspan="3" id="thread_arc" style="width: 100%; background-color: #ffffff; text-align: center;">
										</td>
									</tr>
									<tr>
										<td style="width: 20px; height: 20px;"><img src="images/bottom-left-round.gif" /></td>
										<td style="background-color: #ffffff;">&nbsp;</td>
										<td style="width: 20px; height: 20px;"><img src="images/bottom-right-round.gif" /></td>
									</tr>
								</table>
							</div>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
';

$inBodyTag = ' onload="show_main_page();filter_listing();show_labels();"';

display($output);

?>