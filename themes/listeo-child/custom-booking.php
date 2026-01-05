<link rel="stylesheet" type="text/css" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/css/reserve.css">
<?php

if( isset($_GET) && $_GET['reserve'] == '1' ) {

	$listingName = isset($_GET['listingName']) ? sanitize_text_field($_GET['listingName']) : '';
	$start_date = isset($_GET['stDate']) ? sanitize_text_field($_GET['stDate']) : '';
	$end_date = isset($_GET['enDate']) ? sanitize_text_field($_GET['enDate']) : '';
	$previous_page_id = isset($_GET['page-id']) ? sanitize_text_field($_GET['page-id']) : '';

	$vendor_id   = get_post_field('post_author', $previous_page_id);
	$ven_em = get_the_author_meta('user_email', $vendor_id);


	if($listingName && $listingName != ''){ ?>

		<div class="custom-product">
			<h1><a href="<?php echo get_permalink( $previous_page_id ) ?>">&lt; vers la page du site</a></h1>
		</div>
		
		<div class="woocommerce">
			<div class="woocommerce-checkout">
				<div class="col2-set">
					<div class="smoobu-custom-info-container">
						<h3 class="travel-date">Données sur les voyages</h3>

						<form method="POST" action="<?php echo get_permalink() . '?reserve=2'; ?>" style="width:100%;">

							<div class="smoobu-dates-selection-box">
								<div class="smoobu-date-entry-box">
									<label class="smoobu-date-entry-label" for="stDate">Date d’arrivée</label>
									<input class="smoobu-calendar" type="text" id="stDate" name="stDate" placeholder="Date d’arrivée" value="<?php echo $start_date; ?>" readonly=""><span style="position: absolute; pointer-events: none;" class="easepick-wrapper"></span>
								</div>
								<div class="smoobu-date-entry-box">
									<label class="smoobu-date-entry-label" for="enDate">Date de départ</label>
									<input class="smoobu-calendar" type="text" id="enDate" name="enDate" placeholder="Date de départ" value="<?php echo $end_date; ?>" readonly="">
								</div>
							</div>

							<hr class="smoobu-section-divider">

							<div id="guests-count-container" class="guests-count-container">
								<div class="guest-instruction-container">
									<span class="guest-number-text">Nombre d'invités :</span>
								</div>
								<div class="guests-count-box">
									<div data-max-guest="0" data-free-till="0" class="smoobu-guest-entry-box">
										<label for="_number_of_adults">Adultes</label>
										<select id="_number_of_adults" name="_number_of_adults" class="smoobu-calendar-guests" data-max-adults="2">
											<!-- <option value="">Sélectionner</option> -->
											<option value="1" selected>1</option>
											<option value="2">2</option>
										</select>
									</div>
									<div class="smoobu-guest-entry-box">
										<label for="_number_of_kids">Enfants</label>
										<select id="_number_of_kids" name="_number_of_kids" class="smoobu-calendar-guests" data-max-kids="1">
											<option value="">Sélectionner</option>
											<option value="1">1</option>
											
										</select>
									</div>
								</div>
							</div>

							<hr class="smoobu-section-divider">

							<div class="custom_booking woocommerce-billing-fields">

								<h3 style="margin-top:10px;">Détails de facturation</h3>
						
								<div class="custom_booking_fields woocommerce-billing-fields__field-wrapper">
									
									<p class="form-row form-row-first validate-required" id="booking_first_name_field">
										<label for="booking_first_name" class="required_field">Prénom&nbsp;<span class="required" aria-hidden="true">*</span></label>
										<span class="woocommerce-input-wrapper"><input type="text" class="input-text " name="booking_first_name" id="booking_first_name" placeholder="" value="" aria-required="true" required></span>
									</p>

									<p class="form-row form-row-last validate-required" id="booking_last_name_field">
										<label for="booking_last_name" class="required_field">Nom&nbsp;<span class="required" aria-hidden="true">*</span></label>
										<span class="woocommerce-input-wrapper"><input type="text" class="input-text " name="booking_last_name" id="booking_last_name" placeholder="" value="" aria-required="true" required></span>
									</p>

									<p class="form-row form-row-wide validate-required validate-email" id="booking_email_field">
										<label for="booking_email" class="required_field">E-mail&nbsp;<span class="required" aria-hidden="true">*</span></label>
										<span class="woocommerce-input-wrapper"><input type="email" class="input-text " name="booking_email" id="booking_email" placeholder="" value="" aria-required="true" autocomplete="email" required></span>
									</p>

									<p class="form-row form-row-wide validate-required validate-phone" id="booking_phone_field">
										<label for="booking_phone" class="required_field">Téléphone&nbsp;<span class="required" aria-hidden="true">*</span></label>
										<span class="woocommerce-input-wrapper"><input type="tel" class="input-text " name="booking_phone" id="booking_phone" placeholder="" value="" aria-required="true" autocomplete="tel" required></span>
									</p>

									<p class="form-row form-row-last validate-required" id="booking_country_field">
										<label for="booking_country" class="required_field">Pays&nbsp;<span class="required" aria-hidden="true">*</span></label>
										<span class="woocommerce-input-wrapper"><input type="text" class="input-text " name="booking_country" id="booking_country" placeholder="" value="" aria-required="true" required></span>
									</p>

									<p class="form-row form-row-last validate-required" id="booking_street_field">
										<label for="booking_street" class="required_field">Numéro et nom de rue&nbsp;<span class="required" aria-hidden="true">*</span></label>
										<span class="woocommerce-input-wrapper"><input type="text" class="input-text " name="booking_street" id="booking_street" placeholder="" value="" aria-required="true" required></span>
									</p>

									<p class="form-row form-row-last validate-required" id="booking_city_field">
										<label for="booking_city" class="required_field">Ville&nbsp;<span class="required" aria-hidden="true">*</span></label>
										<span class="woocommerce-input-wrapper"><input type="text" class="input-text " name="booking_city" id="booking_city" placeholder="" value="" aria-required="true" required></span>
									</p>

									<p class="form-row form-row-last validate-required" id="booking_postal_code_field">
										<label for="booking_postal_code" class="required_field">Code postal&nbsp;<span class="required" aria-hidden="true">*</span></label>
										<span class="woocommerce-input-wrapper"><input type="text" class="input-text " name="booking_postal_code" id="booking_postal_code" placeholder="" value="" aria-required="true" required></span>
									</p>

									<input name="listingName" type="hidden" value="<?php echo $listingName; ?>" />
									<input name="venid" type="hidden" value="<?php echo $vendor_id; ?>" />

									<!-- <p class="form-row validate-required woocommerce-validated" id="smoobu_checkin_time_container" data-priority="100">
										<label for="smoobu_checkin_time" class="">Arrivée (hh:mm) : 15:00 heures ou plus tard</label>
										<span class="woocommerce-input-wrapper"><input type="text" class="input-text timepicker" name="smoobu_checkin_time" id="smoobu_checkin_time" placeholder="Check-in (hh:mm)" value="16:30" data-min-time="16:30" min="16:30"></span>
									</p> -->


								</div>
								<button type="submit" class="button alt">Je fais ma demande de réservation</button>
							</div>

						</form>
				</div>
			</div>

		</div>

	<?php }

} else if ( isset($_GET["reserve"]) && $_GET["reserve"] === '2' && $_SERVER['REQUEST_METHOD'] == 'POST' ) {


	$stDate 	= sanitize_text_field($_POST['stDate']);
	$enDate 	= sanitize_text_field($_POST['enDate']);
	$listName 	= sanitize_text_field($_POST['listingName']);
	$venid 		= sanitize_text_field($_POST['venid']);

	$ven_em 	= get_the_author_meta('user_email', $venid);
	$ven_name 	= get_the_author_meta('display_name', $venid);

	$f_name 	= sanitize_text_field($_POST['booking_first_name']);
    $l_name 	= sanitize_text_field($_POST['booking_last_name']);
    $b_email 	= sanitize_email($_POST['booking_email']);
    $b_phone 	= isset($_POST['booking_phone']) ? sanitize_text_field($_POST['booking_phone']) : null;
    $b_country 	= sanitize_text_field($_POST['booking_country']);
    $b_street 	= sanitize_text_field($_POST['booking_street']);
    $b_city 	= sanitize_text_field($_POST['booking_city']);
    $b_p_code 	= sanitize_text_field($_POST['booking_postal_code']);

    $com_add 	= $b_street .', '. $b_p_code . ' ' . $b_city . ', ' . $b_country;

    $b_adults 	= sanitize_text_field($_POST['_number_of_adults']);
    $b_kids 	= isset($_POST['_number_of_kids']) ? sanitize_text_field($_POST['_number_of_kids']) : '0';

    $accept_link = home_url() . '/reserve/?reserve=3&status=1&stDate='.$stDate.'&enDate='.$enDate.'&listName='.$listName.'&venid='.$venid.'&add='.$com_add.'&f_name='.$f_name.'&l_name='.$l_name.'&em='.$b_email.'&ph='.$b_phone.'&adu='.$b_adults.'&kid='.$b_kids;

    $reject_link = home_url() . '/reserve/?reserve=3&status=0&stDate='.$stDate.'&enDate='.$enDate.'&listName='.$listName.'&venid='.$venid.'&add='.$com_add.'&f_name='.$f_name.'&l_name='.$l_name.'&em='.$b_email.'&ph='.$b_phone.'&adu='.$b_adults.'&kid='.$b_kids;

    $headers = [
	    'Content-Type: text/html; charset=UTF-8',
	    'From: Réserve ta Bulle <contact@reserve-ta-bulle.fr>'
	];

	$to1 = $b_email;
    $subject1 = 'Votre demande de réservation a bien été prise en compte !';

    $message1 = '<h3>Bonjour '.$f_name.',</h3>';
    $message1 .= '<p>Nous avons bien reçu votre demande de réservation pour <strong>'.$listName.'</strong>, du <strong>'.$stDate.'</strong> au <strong>'.$enDate.'</strong>.</p>';
    $message1 .= '<p>Votre demande a été transmise à notre partenaire, qui vérifiera la disponibilité de l’hébergement. Nous vous tiendrons informé dès que nous aurons sa réponse.</p>';
    $message1 .= '<p>✅ Si votre demande est acceptée, vous recevrez un email avec un lien pour procéder au paiement et finaliser votre réservation.</p>';
    $message1 .= '<p>❌ Si malheureusement l’hébergement n’est plus disponible, nous vous en informerons et vous proposerons d’autres options si possible.</p>';
    $message1 .= '<p>Cette confirmation peut prendre jusqu’à 72h.  Nous reviendrons vers vous dès que possible.</p>';
    $message1 .= '<p>Si vous avez la moindre question, n’hésitez pas à nous contacter à <a href="mailto:contact@reserve-ta-bulle.fr">contact@reserve-ta-bulle.fr</a>.</p>';
    $message1 .= '<p>À très bientôt,</p>';
    $message1 .= '<p>L’équipe Réserve ta Bulle</p>';

    // Send email to customer
    wp_mail($to1, $subject1, $message1, $headers);
    // echo 'done1 : // Send email to customer<br>';

    echo '<div class="resp_wrap"><h3>Merci pour votre demande !</h3><p>Nous avons bien reçu votre demande de disponibilité pour notre tente bulle.</p><p>Nous vous contacterons dans les plus brefs délais pour vous confirmer les disponibilités et répondre à vos questions.</p><p>À très bientôt !</p>';


    $to2 = array(
		$ven_em,
		// 'majid.workwp@gmail.com',
		'contact@reserve-ta-bulle.fr',
	);
    $subject2 = 'Nouvelle demande de réservation à valider – ' . $listName;

    $message2 = '<h3>Bonjour,</h3>';
    $message2 .= '<p>Un client vient d’effectuer une demande de réservation pour votre hébergement <strong>'.$listName.'</strong>.</p>';
    $message2 .= '<ul>';
    $message2 .= '<li>Client : <strong>'. $f_name .' '. $l_name .'</strong></li>';
    $message2 .= '<li>Dates : du <strong>'. $stDate .'</strong> au <strong>'. $enDate .'</strong></li>';
    $message2 .= '<li>Nombre de personnes : <strong>'. $b_adults .'</strong>(Adultes) : <strong>'. $b_kids .'</strong>(Les enfants) </li>';
    $message2 .= '</ul>';
    $message2 .= '<p>Merci de nous confirmer la disponibilité de votre hébergement dans les plus brefs délais afin que nous puissions informer le client et lui envoyer le lien de paiement.</p>';

    $message2 .= '<p>✅ Si vous acceptez la demande, merci de cliquer sur “accepter”</p>';
    $message2 .= '<a href="'.$accept_link.'" style="padding:8px 22px;margin-bottom:30px;background-color:green;color:#fff;border-radius:2px;border:none;display:inline-block;" target="_blank">accepter</a>';

    $message2 .= '<p>❌ Si l’hébergement n’est pas disponible, merci de cliquer sur “refuser”</p>';
    $message2 .= '<a href="'.$reject_link.'" style="padding:8px 22px;margin-bottom:30px;background-color:#e74040;color:#fff;border-radius:2px;border:none;display:inline-block;" target="_blank">refuser</a>';

    $message2 .= '<p>Pour toute question, n’hésitez pas à nous contacter.</p>';
    $message2 .= '<p>Dans l’attente de votre retour,</p>';
    $message2 .= '<p>L’équipe Réserve ta Bulle</p>';

    // Send email to vendor and admin
    wp_mail($to2, $subject2, $message2, $headers);
    // echo 'done2 : // Send email to vendor and admin<br>';

} else if ( isset($_GET["reserve"]) && $_GET["reserve"] === '3' && isset($_GET["status"]) ){

	$stDate 	= sanitize_text_field($_GET['stDate']);
	$enDate 	= sanitize_text_field($_GET['enDate']);
	$listName 	= sanitize_text_field($_GET['listName']);
	$venid 		= sanitize_text_field($_GET['venid']);
	$com_add 	= sanitize_text_field($_GET['add']);
	$f_name 	= sanitize_text_field($_GET['f_name']);
	$l_name 	= sanitize_text_field($_GET['l_name']);
	$em 		= sanitize_email($_GET['em']);
	$ph 		= sanitize_text_field($_GET['ph']);

	$adu 		= sanitize_text_field($_GET['adu']);
	$kid 		= sanitize_text_field($_GET['kid']);

	$ven_em 	= get_the_author_meta('user_email', $venid);
	$ven_name 	= get_the_author_meta('display_name', $venid);

    $headers = [
	    'Content-Type: text/html; charset=UTF-8',
	    'From: Réserve ta Bulle <dev@mathesconsulting.de>'
	];

	if ( isset($_GET["status"]) && $_GET["status"] === '1' ){ // when accept

		// $to3 = 'majid.workwp@gmail.com';
		$to3 = 'contact@reserve-ta-bulle.fr';

	    $subject3 = 'Demande de réservation acceptée – '. $f_name .' '. $l_name;

	    $message3 = '<h3>Bonjour équipe,</h3>';
	    $message3 .= '<p>Le partenaire <strong>'. $ven_name .'</strong> a accepté la demande de réservation suivante. Voici les détails fournis par le client :</p>';

	    $message3 .= '<p>Informations du client :</p>';
	    $message3 .= '<ul>';
	    $message3 .= '<li>Nom et prénom : <strong>'. $f_name .' '. $l_name .'</strong></li>';
	    $message3 .= '<li>Adresse : '. $com_add . '</li>';
	    $message3 .= '<li>Téléphone : '. $ph . '</li>';
	    $message3 .= '<li>Email : '. $em . '</li>';
	    $message3 .= '</ul>';

	    $message3 .= '<p>Détails de la réservation :</p>';

	    $message3 .= '<ul>';
	    $message3 .= '<li>Hébergement : <strong>'. $listName .'</strong></li>';
    	$message3 .= '<li>Dates : du <strong>'. $stDate .'</strong> au <strong>'. $enDate .'</strong></li>';
    	$message3 .= '<li>Nombre de personnes : <strong>'. $adu .'</strong>(Adultes) : <strong>'. $kid .'</strong>(Les enfants) </li>';
	    $message3 .= '</ul>';

	    $message3 .= '<p>✅ Prochaine étape : Envoyer au client le lien de paiement pour finaliser la réservation.</p>';
	    $message3 .= '<p>Merci de traiter cette demande dès que possible et d’en informer le partenaire une fois le paiement effectué.</p>';

	    // Send email to admin when accepted
	    wp_mail($to3, $subject3, $message3, $headers);
	    // echo 'done3 : // Send email to admin when accepted<br>';

	    echo '<div class="resp_wrap"><h3>Merci pour votre réponse !</h3><p>Nous avons bien reçu votre acceptation.</p><p>Nous allons maintenant transmettre le lien de paiement au client afin de finaliser la réservation.</p>';

	} else if( isset($_GET["status"]) && $_GET["status"] === '0' ) { // when reject

		// $to4 = 'majid.workwp@gmail.com';
		$to4 = 'contact@reserve-ta-bulle.fr';

	    $subject4 = 'Demande de réservation refusée – '. $f_name .' '. $l_name;

	    $message4 = '<h3>Bonjour équipe,</h3>';
	    $message4 .= '<p>Le partenaire <strong>'.$ven_name.'</strong> a refusé la demande de réservation suivante. Voici les informations fournies par le client :</p>';

	    $message4 .= '<p>Informations du client :</p>';
	    $message4 .= '<ul>';
	    $message4 .= '<li>Nom et prénom : <strong>'. $f_name .' '. $l_name .'</strong></li>';
	    $message4 .= '<li>Adresse : '. $com_add . '</li>';
	    $message4 .= '<li>Téléphone : '. $ph . '</li>';
	    $message4 .= '<li>Email : '. $em . '</li>';
	    $message4 .= '</ul>';

	    $message4 .= '<p>Détails de la réservation :</p>';

	    $message4 .= '<ul>';
	    $message4 .= '<li>Hébergement : <strong>'. $listName .'</strong></li>';
    	$message4 .= '<li>Dates : du <strong>'. $stDate .'</strong> au <strong>'. $enDate .'</strong></li>';
    	$message4 .= '<li>Nombre de personnes : <strong>'. $adu .'</strong>(Adultes) : <strong>'. $kid .'</strong>(Les enfants) </li>';
	    $message4 .= '</ul>';

	    $message4 .= '<p>❌ Statut : Réservation refusée par le partenaire</p>';
	    $message4 .= '<p>Le client a été informé du refus. Si des alternatives sont disponibles, nous pourrions lui proposer d’autres dates ou hébergements similaires.</p>';

	    // Send email to admin when refused
	    wp_mail($to4, $subject4, $message4, $headers);
	    // echo 'done4 : // Send email to admin when refused<br>';

	    echo '<div class="resp_wrap"><h3>Merci pour votre retour.</h3><p>Nous avons bien pris en compte votre refus de disponibilité.</p><p>Le client en sera informé et nous ne donnerons pas suite à la demande de réservation.</p>';



		$to5 = $em;

	    $subject5 = 'Mise à jour de votre demande de réservation';

	    $message5 = '<h3>Bonjour '.$f_name.',</h3>';
	    $message5 .= '<p>Nous revenons vers vous concernant votre demande de réservation pour <strong>'.$listName.', '.$stDate.' au '.$enDate.'</strong>.</p>';

	    $message5 .= '<p>Malheureusement, l’hébergement n’est plus disponible à ces dates. Nous en sommes désolés.</p>';
	    $message5 .= '<p>Si vous le souhaitez, nous pouvons vous proposer d’autres dates disponibles ou des hébergements similaires. N’hésitez pas à nous contacter pour en discuter.</p>';
	    $message5 .= '<p>Pour toute question ou pour explorer d’autres options, vous pouvez nous écrire à <a href="mailto:contact@reserve-ta-bulle.fr">contact@reserve-ta-bulle.fr</a></p>';
	    $message5 .= '<p>Merci pour votre compréhension, et nous espérons pouvoir vous accueillir prochainement.</p>';
	    $message5 .= '<p>L’équipe Réserve ta Bulle</p>';

	    // Send email to customer when refused
	    wp_mail($to5, $subject5, $message5, $headers);
	    // echo 'done5 : // Send email to customer when refused<br>';

	}

}