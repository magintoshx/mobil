import React from 'react';
import {View, Text,Image,TouchableOpacity,Dimensions,StatusBar,Alert} from 'react-native';
import {Icon, Container} from 'native-base';
import { GooglePlacesAutocomplete } from 'react-native-google-places-autocomplete';


const haritaSecim = (props) => (
<Container>
     <GooglePlacesAutocomplete
      placeholder='Konum ara'
      minLength={4}
      autoFocus={true}
      returnKeyType={'search'} // Can be left out for default return key https://facebook.github.io/react-native/docs/textinput.html#returnkeytype
      keyboardAppearance={'light'} // Can be left out for default keyboardAppearance https://facebook.github.io/react-native/docs/textinput.html#keyboardappearance
      listViewDisplayed='false'    // true/false/undefined
      fetchDetails={true}
  	  myKonum = {(veriler)=>{
  		  let h = props.navigation.getParam("myKonum");
  		  h(veriler);
  		  props.navigation.goBack();
  	  }}
      renderDescription={row => row.description} // custom description render
      onPress={(data, details = null) => { // 'details' is provided when fetchDetails = true
        if(!details){
			Alert.alert(
			  'Bilgilendirme', //Virgül önemli
			  'Lokasyon bilgilerine ulaşılamadı. Lütfen başka bir noktayı deneyin.'
			  [
				{ text: 'Tamam', onPress: () => null }
			  ]
			);
		}else{
			let fun = props.navigation.getParam("veriIsle");
			fun(data,details);
			props.navigation.goBack();
		}
      }}

      getDefaultValue={() => ''}

      query={{
        // available options: https://developers.google.com/places/web-service/autocomplete
        key: 'AIzaSyDFg2un3oW_CqEbCFthIBc9dolH-mHLo1U',
        language: 'tr',
			components: "country:tr"
      }}

      styles={{
		  container:{marginTop:StatusBar.currentHeight},
        textInputContainer: {
          width: '100%'
        },
		textInput:{
			height:40
		},
		textInputContainer:{backgroundColor:"#F39200", height:57},
        description: {
          fontWeight: 'bold'
        },
        predefinedPlacesDescription: {
          color: '#F39200'
        }
      }}
      currentLocation={true}
      currentLocationLabel="Mevcut Konumum"
      nearbyPlacesAPI='GooglePlacesSearch' // Which API to use: GoogleReverseGeocoding or GooglePlacesSearch
      GoogleReverseGeocodingQuery={{
        // available options for GoogleReverseGeocoding API : https://developers.google.com/maps/documentation/geocoding/intro
      }}

      filterReverseGeocodingByTypes={['locality']} // filter the reverse geocoding results by types - ['locality', 'administrative_area_level_3'] if you want to display only cities

      debounce={200} // debounce the requests in ms. Set to 0 to remove debounce. By default 0ms.
      renderLeftButton={() => <TouchableOpacity onPress={()=>props.navigation.goBack()} style={{justifyContent:"center", alignItems:"center", paddingLeft:10, paddingRight:10}}><Icon style={{color:"#fff"}} name="angle-left" type="FontAwesome" /></TouchableOpacity>}
    />
	</Container>
);
export default haritaSecim;
