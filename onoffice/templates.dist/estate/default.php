<?php

/**
 *
 *    Copyright (C) 2016  onOffice Software AG
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 *  Default template
 */

/* @var $pEstates onOffice\WPlugin\EstateList */

?>

<h1>Übersicht der dargestellten Objekte</h1>

<?php
$pMaps = new onOffice\WPlugin\Maps\GoogleMap();
$pMaps->setWidth(630);
$pMaps->setHeight(350);

while ( $currentEstate = $pEstates->estateIterator() ) {
	$position = array(
		'lat' => (float) $currentEstate['breitengrad'],
		'lng' => (float) $currentEstate['laengengrad'],
	);
	$title = $currentEstate['objekttitel'];

	if ( ! $position['lng'] || ! $position['lat'] || ! $currentEstate['showGoogleMap'] ) {
		continue;
	}

	$pMaps->addNewMarker(
		$position['lng'], $position['lat'],	! $currentEstate['virtualAddress'], $title );
}
echo $pMaps->render();

$pEstates->resetEstateIterator();
?>

<?php while ( $currentEstate = $pEstates->estateIterator() ) : ?>

<p>
	<a href="<?php echo $pEstates->getEstateLink('detail'); ?>">Zur Detailansicht</a><br>
	<?php foreach ( $currentEstate as $field => $value ) :
		if ( is_numeric( $value ) && 0 == $value ) {
			continue;
		}
	?>

		<?php echo $pEstates->getFieldLabel( $field ) .': '.$value; ?><br>

	<?php endforeach; ?>


	<?php
	foreach ( $pEstates->getEstateContacts() as $contactData ) : ?>
	<p>
		<b>ASP: <?php echo $contactData['Vorname']; ?> <?php echo $contactData['Name']; ?></b><br>
		<img src="<?php echo $contactData['imageUrl']; ?>">
		<ul>
			<li>Telefon: <?php echo $contactData['defaultphone']; ?></li>
			<li>Telefax: <?php echo $contactData['defaultfax']; ?></li>
			<li>E-Mail: <?php echo $contactData['defaultemail']; ?></li>
		</ul>
	</p>
	<?php endforeach; ?>

	<p><b>Kontaktformular:</b>
		<?php
			$pForm = new \onOffice\WPlugin\Form( 'estatelistcontactform', 'DEU' );

			include( __DIR__ . "/../form/defaultform.php" );
		?>
	</p>


	<?php
	$estatePictures = $pEstates->getEstatePictures();

	foreach ( $estatePictures as $id ) : ?>
	<a href="<?php echo $pEstates->getEstatePictureUrl( $id ); ?>">
		<img src="<?php echo $pEstates->getEstatePictureUrl( $id, array('width' => 400, 'height' => 300) ); ?>">
	</a>
	<?php endforeach; ?>
</p>

<?php endwhile; ?>