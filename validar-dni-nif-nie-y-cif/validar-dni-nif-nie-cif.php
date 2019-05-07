<?php
/*
Plugin Name: Validar CI ecuatoriana
Plugin URI: http://www.hodeidesign.com
Description: Valida CI en Ecuadir utilizando el plugin Contact Form 7
Version: 1.1
Author: Hodei Design / Danilo Nieto
Author URI: http://www.hodeidesign.com
License: GPL2

#_________________________________________________ LICENSE

Copyright 2014 Hodei Design (email : hola@hodeidesign.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );


function cf7_requerido()
{
	$plugin_messages = '';
	
	 if ( file_exists( WP_PLUGIN_DIR . '/contact-form-7/wp-contact-form-7.php' ) ){
		if(!is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ))
		{
			$plugin_messages = 'El plugin validación CI ecuatoriana requiere que el plugin Contact Form 7 esté activado';
		}
	 }else{
		// Download contact form 7
		$plugin_messages = 'El plugin validación CI requiere el plugin Contact Form 7, <a href="https://wordpress.org/plugins/contact-form-7/">descargalo aquí</a>.';
	 }
	
	if(!empty($plugin_messages))
	{
		echo '<div id="message" class="error">';

			
			echo '<p><strong>'.$plugin_messages.'</strong></p>';


		echo '</div>';
	}
}

add_action('admin_notices', 'cf7_requerido');

if ( file_exists( WP_PLUGIN_DIR . '/contact-form-7/wp-contact-form-7.php' ) ){
	if(is_plugin_active( 'contact-form-7/wp-contact-form-7.php' )){
		// Lógica validación
		function valida_nif_cif_nie($identidad) {
/**
     * Algoritmo para validar cedulas de Ecuador
     * @Pasos  del algoritmo
     * 1.- Se debe validar que tenga 10 numeros
     * 2.- Se extrae los dos primero digitos de la izquierda y compruebo que existan las regiones
     * 3.- Extraigo el ultimo digito de la cedula
     * 4.- Extraigo Todos los pares y los sumo
     * 5.- Extraigo Los impares los multiplico x 2 si el numero resultante es mayor a 9 le restamos 9 al resultante
     * 6.- Extraigo el primer Digito de la suma (sumaPares + sumaImpares)
     * 7.- Conseguimos la decena inmediata del digito extraido del paso 6 (digito + 1) * 10
     * 8.- restamos la decena inmediata - suma / si la suma nos resulta 10, el decimo digito es cero
     * 9.- Paso 9 Comparamos el digito resultante con el ultimo digito de la cedula si son iguales todo OK sino existe error.     
     */
    $strCedula = $identidad;
    //compruebar longitud de 10 digitos
    if (strlen($strCedula) == 10){ 
			$nro_region = substr($strCedula, 0, 2); //extraigo los dos primeros caracteres de izq a der
			if ($nro_region >= 1 && $nro_region <= 24)
				{ // compruebo a que region pertenece esta cedula//
				$ult_digito = substr($strCedula, -1, 1); //extraigo el ultimo digito de la cedula

				// extraigo los valores pares//

				$valor2 = substr($strCedula, 1, 1);
				$valor4 = substr($strCedula, 3, 1);
				$valor6 = substr($strCedula, 5, 1);
				$valor8 = substr($strCedula, 7, 1);
				$suma_pares = ($valor2 + $valor4 + $valor6 + $valor8);

				// extraigo los valores impares//

				$valor1 = substr($strCedula, 0, 1)*2;
				if ($valor1 > 9)
					$valor1 = ($valor1 - 9);
					
				$valor3 = substr($strCedula, 2, 1)*2;
				if ($valor3 > 9)
					$valor3 = ($valor3 - 9);

				$valor5 = substr($strCedula, 4, 1)*2;
				if ($valor5 > 9)
					$valor5 = ($valor5 - 9);

				$valor7 = substr($strCedula, 6, 1)*2;
				if ($valor7 > 9)
					$valor7 = ($valor7 - 9);

				$valor9 = substr($strCedula, 8, 1)*2;
				if ($valor9 > 9)
					$valor9 = ($valor9 - 9);

				$suma_impares = ($valor1 + $valor3 + $valor5 + $valor7 + $valor9);
				$suma = ($suma_pares + $suma_impares);
				$dis = substr($suma, 0, 1); //extraigo el primer numero de la suma
				$dis = (($dis + 1) * 10); //luego ese numero lo multiplico x 10, consiguiendo asi la decena inmediata superior
				$digito = ($dis - $suma);
				//si suma = 10, el decimo digito = cero
				if ($digito == 10)
					$digito = '0';
                
				if ($digito == $ult_digito)
					{ //comparo los digitos final y ultimo
					return 1;
					}
				  else
					{
					return 0;
					}
				}
			  else
				{
				return 0;
				}
			}
		  else
			{
			return 0;
			}
		}
    
		}

		function cf7_nif_cif_nie_validacion($result, $tag) {
			$result_actual = $result['valid'];

			//$type = $tag['type'];
			$type = $tag['basetype'];
			$name = $tag['name'];
			//Si esta vacio y es obligatorio lanzar error invalid_required
			if($type == 'text*' && $_POST[$name] == ''){
					//$result['valid'] = false;
					//$result['reason'][$name] = wpcf7_get_message( 'invalid_required' );
					$result->invalidate( $tag, wpcf7_get_message( 'invalid_required' ) );	
			}
			
			//validaciones

			//dni,cif,nie o nif 
			if($name == 'identidad') {
				$identidad = $_POST['identidad'];
				
				if($identidad != '') {
					if(valida_nif_cif_nie($identidad) == 0){
						$result->invalidate( $tag, wpcf7_get_message( 'validation_error' ) );						

					}else{
						if($result_actual == false){
							$result['valid'] = false;
						}else{
							$result['valid'] = true;
						}
					}			
				}
			}

			
			return $result;
			
		}

		//add filter para validación text
		add_filter( 'wpcf7_validate_text', 'cf7_nif_cif_nie_validacion', 10, 2 );
		add_filter( 'wpcf7_validate_text*', 'cf7_nif_cif_nie_validacion', 10, 2 );
	}


?>
