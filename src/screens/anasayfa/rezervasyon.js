import React, { Component } from "react";
import { ImageBackground, View, StatusBar,Image,Dimensions,StyleSheet,AppRegistry,ActivityIndicator,TouchableOpacity, ScrollView, Alert,AsyncStorage, BackHandler,Platform,Keyboard,Linking} from "react-native";
import NetInfo from "@react-native-community/netinfo";
import Geolocation from 'react-native-geolocation-service';
import { SafeAreaConsumer } from 'react-native-safe-area-context';
import moment from 'moment';

require('moment/locale/tr.js');

import RNGooglePlaces from 'react-native-google-places';
import DateTimePicker from '@react-native-community/datetimepicker';

import { Container, Header, Title, Content, Text, Button, Icon, Left, Right, Body, Toast, Input, Item,Badge, Spinner,Picker, Form, Thumbnail, Textarea} from "native-base";
import MapView, { PROVIDER_GOOGLE, Marker }  from 'react-native-maps';
import styles from "./styles";
import styless from "./styless";
import MapViewDirections from 'react-native-maps-directions';
import StarRating from 'react-native-star-rating';
import SplashScreen from 'react-native-splash-screen';
import { NavigationActions } from 'react-navigation'
import call from 'react-native-phone-call'

const logo = require("../../../assets/logo.png");
const alarm = require("../../../assets/alarm.png");

const launchscreenBg = require("../../../assets/launchscreen-bg.png");
const launchscreenLogo = require("../../../assets/logo-kitchen-sink.png");
const headeroverlay = require("../../../assets/headeroverlay.png");
const deviceHeight = Dimensions.get("window").height;
const deviceWidth = Dimensions.get("window").width;
import GPSState from 'react-native-gps-state';
import MarqueeText from 'react-native-marquee';
import OneSignal from 'react-native-onesignal';
import {check, PERMISSIONS,request} from 'react-native-permissions';
import Modal from 'react-native-modalbox';

function MiniOfflineSign() {
  return (
    <View style={styles.offlineContainer}>
      <Text style={styles.offlineText}>İnternet bağlantınızda sorun var. Lütfen konrol edin.</Text>
    </View>
  );
}

class Anasayfa extends Component {
  openSearchModal = (veris, detay) => {
    /*RNGooglePlaces.openAutocompleteModal({
		 locationBias: {
                latitudeSW: this.state.latitude,
                latitudeNE: this.state.latitude,
                longitudeSW:  this.state.longitude,
                longitudeNE:  this.state.longitude
            }
			})
    .then((place) => {
      this.setState({neredentext: place.address, neredenlat: place.location.latitude, neredenlng: place.location.longitude,secimasama:false});
      console.log(place);
    })
    .catch(error => console.log(error.message));*/
	this.setState({
		neredentext:veris.description, neredenlat: detay.geometry.location.lat, neredenlng:detay.geometry.location.lng, secimasama:false
	});
  }
  openSearchModalNereye = (veris, detay) => {
   /* RNGooglePlaces.openAutocompleteModal({
		locationBias: {
                 latitudeSW: this.state.latitude,
                latitudeNE: this.state.latitude,
                longitudeSW:  this.state.longitude,
                longitudeNE:  this.state.longitude
            }
	})
    .then((place) => {
      this.setState({nereyetext: place.address, nereyelat: place.location.latitude, nereyelng: place.location.longitude, radius:10,secimasama:false});
      console.log(place);
    })
    .catch(error => console.log(error.message));*/
	this.setState({
		nereyetext:veris.description, nereyelat: detay.geometry.location.lat, nereyelng:detay.geometry.location.lng,radius:10, secimasama:false
	});
  }
	myKonum =(ve) =>{
		console.log(ve);
		if(!ve || !ve.results || ve.results.length<1){
			Alert.alert(
			  'Bilgilendirme', //Virgül önemli
			  'Lokasyon bilgilerine ulaşılamadı. Lütfen başka bir noktayı deneyin.'
			  [
				{ text: 'Tamam', onPress: () => null }
			  ]
			);
		}else{
			let h = ve.results[0];
			this.setState({
				neredentext:h.formatted_address, neredenlat: h.geometry.location.lat, neredenlng:h.geometry.location.lng, secimasama:false
			});
		}
	}
	myKonum1 =(ve) =>{
		console.log(ve);
		if(!ve || !ve.results || ve.results.length<1){
			Alert.alert(
			  'Bilgilendirme', //Virgül önemli
			  'Lokasyon bilgilerine ulaşılamadı. Lütfen başka bir noktayı deneyin.'
			  [
				{ text: 'Tamam', onPress: () => null }
			  ]
			);
		}else{
			let h = ve.results[0];
			this.setState({
				nereyetext:h.formatted_address, nereyelat: h.geometry.location.lat, nereyelng:h.geometry.location.lng,radius:10, secimasama:false
			});
		}
	}
  konumumual(){
    try{
      Geolocation.getCurrentPosition(
        (position) => {
			let newRegion = {
  latitude: position.coords.latitude,
  longitude: position.coords.longitude,
  latitudeDelta: this.state.latdelta,
  longitudeDelta: this.state.lngdelta,
};
if(this.mapRef){
	this.mapRef.animateToRegion(newRegion, 500);
}
          this.setState({
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            error: null,
            loader:false
          });
        },
        (error) => {this.setState({ error: error.message });

		if(error.code == 3){
					  this.konumumual();
				  }else if(error.code == 5){
					   BackHandler.exitApp();
				  }

      },
        { enableHighAccuracy: false},
      );
    }catch(e){
      console.log(e);
    }
  }
  getArac = (id) => {
    this.setState({loader:true,secilenAracTipi:id});
    var data = this.state.araclistesi.map(function(item) {
      if(item.id == id){
        item.secilmis = true;
      }else{
        item.secilmis = false;
      }
      return item;
    });
    this.getirSecimArac(id);
  }

  getirSecimArac(id){
    if(!id){
      Alert.alert("Bilgilendirme","Araç tipi seçimi yapınız",[
        {text: 'Kapat', onPress: () => null}
      ]);
    }else{
      fetch(global.apiurl,
            {
                method: 'POST',
                headers:
                {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(
                {
                  p : 'start',
                  s : 6,
                  userid:global.userid,
                  token:global.token,
                  cartype:id
                })

            }).then((response) => response.json()).then((jr) =>
            {
				console.log(jr);
              if(jr["status"] == 1){
                this.haritapoiicon = jr["cartype"].haritaikon;
                this.setState({ toplamucret: (this.state.aradakimesafe * jr.cartype.km_rate).toFixed(1),kmbasiucret:jr.cartype.km_rate, arackisisayi:jr.cartype.kisi_sayisi,haritapois:jr.message, loader:false});
              }else{
                Alert.alert("Bilgilendirme",jr.message,[
                  {text: 'Kapat', onPress: () => null}
                ]);
              }
            }).catch((error) =>
            {
                Alert.alert("Bilgilendirme","Hata Oluştu: "+JSON.stringify(error),[
                  {text: 'Kapat', onPress: () => null}
                ]);
                this.setState({ loader : false});
            });
    }
  }
  getAsama2 = () => {
    if(!this.state.secilenAracTipi) Alert.alert("Lütfen bir araç seçimi yapınız");
    else if((parseFloat(this.state.aradakimesafe)).toFixed(1)<0.5){
        Alert.alert("VipUpp","Gitmek istediğiniz noktalar arası mesafeyi hesaplayamadık. Lütfen tekrar deneyin");
    }
    else{
        this.setState({toplamucret: (parseFloat(this.state.aradakimesafe)*parseFloat(this.state.kmbasiucret)).toFixed(1),secimasama:true,seyehatlistesi:null});
    }
  }
  driverCall = () => {
    Linking.openURL('http://api.bulutsantralim.com/bridge?key=K0cee7222-329c-4984-bded-1f75a2c5185c&source='+this.state.driverphone+'&destination='+this.state.musteriphone+'')
  }

messageSend = (id) =>{
  this.setState({ loader : true});
  fetch(global.apiurl,
        {
            method: 'POST',
            headers:
            {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(
            {
              p : 'message',
              s : 5,
              userid:global.userid,
              token:global.token,
              driverid:id
            })
        }).then((response) => response.json()).then((jr) =>
        {
          if(jr["status"] == 1){
            global.chatid = jr.message;
            this.props.navigation.navigate("mesajlas");
          }else{
            Alert.alert("Bilgilendirme",jr.message,[
              {text: 'Kapat', onPress: () => null}
            ]);
          }
          this.setState({ loader : false});
        }).catch((error) =>
        {
            Alert.alert("Bilgilendirme","Hata Oluştu: "+JSON.stringify(error),[
              {text: 'Kapat', onPress: () => null}
            ]);
            this.setState({ loader : false});
        });
}
  constructor(props) {
      super(props);
      getArac = this.getArac.bind(this);
      getAsama2 = this.getAsama2;
      messageSend = this.messageSend;
      this.handleBackPress = this.handleBackPress.bind(this);
      this.state = {
        latitude: null,
        longitude: null,
        error: null,
        loader: true,
        neredentext: "",
        nereyetext:"",
        neredenlat: null,
        neredenlng: null,
        nereyelat: null,
        nereyelng: null,
        secimKategoriId: null,
        secilenAracTipi:null,
        asama2gec:false,
        asama3gec:false,
        aradakimesafe:0,
        arasikacdk:0,
        araclistesi: null,
        haritapois:null,
        kmbasiucret:0,
        arackisisayi:0,
        seyehatlistesi:null,
        musteriphone:null,
        driverphone:null,
        isConnected: true,
        latdelta:0.01522,
          secimasama:false,
          odemeForm:false,
          selected:1,
          kartno:"",
          kartay:"",
          kartyil:"",
          cvv:"",
          karttipi:"",
          formyukari:1,
          odemetipisecilen:"",
          aracpois:[],
          tarihOpen:false,
          secilenTarih:null,
          saatOpen:false,
          saatSecilen:null,
        lngdelta:Dimensions.get("window").width / Dimensions.get("window").height * 0.01522
      };
	  this.mylat=0;
	  this.mylng=0;
      this.marqueetext = "";
	  this.haritapoiicon="";
    }
    async oturumkapanmis(){
      await AsyncStorage.removeItem('token');
      await AsyncStorage.removeItem('userid');
      let routeName = "Home";
      let action = NavigationActions.init({ routeName });
      if (!action.routeName) {
        action.routeName = routeName;
      }
      const resetToHome = NavigationActions.reset({
        index: 0,
        actions: [
          action
        ],
      });
      this.props.navigation.dispatch(resetToHome);
      this.setState({ loader : false });
    }

    tarihDegistir = (event, selectedDate) => {
      this.setState({tarihOpen:false,secilenTarih:moment(selectedDate).format('L')})
    };

    saatDegistir = (event, selectedDate) => {
      this.setState({saatOpen:false,saatSecilen:moment(selectedDate).format('HH:m')})
    };
    puanla(id) {
       this.setState({loader: true,
asama2gec:false
       });
       fetch(global.apiurl,
           {
               method: 'POST',
               headers: {
                   'Accept': 'application/json',
                   'Content-Type': 'application/json',
               },
               body: JSON.stringify(
                   {
                       p: 'start',
                       s: 14,
                       userid: global.userid,
                       token: global.token,
                       id: id
                   })

           }).then((response) => response.json()).then((jr) => {
           console.log(jr);
           if (jr["status"] == 1) {
               this.refs.modal1.open();
               this.setState({
                   puanlaId: id,
                   drivername: jr.message.name,
                   driveravatar: jr.message.avatar,
                   drivercarplate: jr.message.car_plate,
                   loader: false
               });
           } else {
               Alert.alert("Bilgilendirme", jr.message, [
                   {text: 'Kapat', onPress: () => this.setState({loader: false})}
               ]);
           }

       }).catch((error) => {
           console.log(error);
           this.setState({loader: false});
       });
   }

konumMesafeIslemleri(data){
  let url = "https://maps.googleapis.com/maps/api/directions/json?origin="+data.carlat+","+data.carlng+"&destination="+this.state.latitude+","+this.state.longitude+"&language=tr-TR&sensor=false&mode=%22DRIVING%22&key=AIzaSyAqc2J59eq2rLYhgnAlqIitylenG3NOy9k";
  fetch(url,
      {
          method: 'GET',
          headers: {
              'Accept': 'application/json',
              'Content-Type': 'application/json',
          },
          body: null
      }).then((response) => response.json()).then((jr) => {
        this.setState({
          surucuDakika:jr["routes"][0]["legs"][0]["duration"]["text"],
          surucuMesafe:jr["routes"][0]["legs"][0]["distance"]["text"]
        });
  }).catch((error) => {

  });
}
    getMyApp(){
      fetch(global.apiurl,
            {
                method: 'POST',
                headers:
                {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(
                {
                  p : 'start',
                  s : 3,
                  userid:global.userid,
                  token:global.token,
				  mylat:this.mylat,
				  mylng:this.mylng
                })

            }).then((response) => response.json()).then((jr) =>
            {
              if(jr["status"] == 1){
                  this.marqueetext = jr.mesaj;
                  console.log(jr.message.aracpois);

                this.setState({
                  seyehatlistesi:jr.message.travel_list?jr.message.travel_list:null, musteriphone:jr.message.musteriphone, driverphone:jr.message.driverphone,
                   aracpois:jr.message.aracpois
                });
                if(!this.state.araclistesi){
                  this.setState({
                    araclistesi:jr.message.carlist
                  });
                }
                if(jr.message.travel_list){
                  if(jr.message.travel_list.status == 1){
                    this.konumMesafeIslemleri(jr.message.travel_list.driver);
                  //  console.log(jr.message.travel_list);
                  }
                  if(jr.message.travel_list.status == 2)
                  {
                    console.log(jr.message.travel_list);
                     this.puanla(jr.message.travel_list.id);

                   }else{
                   this.setState({
                    arackisisayi:jr.message.car_list?jr.message.car_list.kisi_sayisi:null,
                    aradakimesafe:jr.message.travel_list?jr.message.travel_list.km:null,
                    toplamucret:((jr.message.travel_list?jr.message.travel_list.km:0) * (jr.message.car_list?jr.message.car_list.km_rate:0)).toFixed(1),
                    kmbasiucret:jr.message.car_list?jr.message.car_list.km_rate:null
                  });
                   }
                }else{
                  this.setState({
                    asama2gec:null,
                    asama3gec:null
                  });
                }
                global.bildirim = jr.message.bildirimSayi;

                SplashScreen.hide();
                //BackHandler.addEventListener('hardwareBackPress',this.handleBackPress);

              }else{
                this.oturumkapanmis();
              }
            }).catch((error) =>
            {
              /*  Alert.alert("Bilgilendirme","Hata Oluştu: "+JSON.stringify(error),[
                  {text: 'Kapat', onPress: () => null}
                ]);*/
                console.log(error);
                this.setState({ loader : false});
            });

    }
    onStarRatingPress(rating) {
 this.setState({
     starCount: rating
 });
}

seyahatPuanla() {

       if (!this.state.starCount) {
           Alert.alert("Bilgilendirme", "Lütfen puanınızı belirtin", [
               {text: 'Kapat', onPress: () => null}
           ]);
       } else {
           this.setState({loader: true});
           fetch(global.apiurl,
               {
                   method: 'POST',
                   headers: {
                       'Accept': 'application/json',
                       'Content-Type': 'application/json',
                   },
                   body: JSON.stringify(
                       {
                           p: 'start',
                           s: 13,
                           userid: global.userid,
                           token: global.token,
                           s_id: this.state.puanlaId,
                           srate: this.state.starCount,
                           yorum: this.state.yorum
                       })

               }).then((response) => response.json()).then((jr) => {
               this.refs.modal1.close();

               Alert.alert("Bilgilendirme", jr.message, [
                   {text: 'Kapat', onPress: () => this.setState({loader: false})}
               ]);

           }).catch((error) => {
               Alert.alert("Bilgilendirme", "Hata Oluştu: " + JSON.stringify(error), [
                   {text: 'Kapat', onPress: () => this.setState({loader: false})}
               ]);
           });
       }
   }

    iptalEtTrasfer(){
      Alert.alert("Bilgilendirme","Aktif araç yönlendirmesinin iptal olmasını istiyor musunuz?",[
        {text: 'Evet', onPress: () => {
          this.setState({ loader : true});
          fetch(global.apiurl,
                {
                    method: 'POST',
                    headers:
                    {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(
                    {
                      p : 'start',
                      s : 10,
                      userid:global.userid,
                      token:global.token
                    })

                }).then((response) => response.json()).then((jr) =>
                {
                  if(jr["status"] == 1){
                    this.setState({
                      seyehatlistesi:null, asama2gec:false, asama3gec:false, neredentext:"", nereyetext:"", haritapois:null
                    });
                  }
                  Alert.alert("Bilgilendirme",jr.message,[
                    {text: 'Kapat', onPress: () => null}
                  ]);
                  this.setState({ loader : false});
                }).catch((error) =>
                {
                    Alert.alert("Bilgilendirme","Hata Oluştu: "+JSON.stringify(error),[
                      {text: 'Kapat', onPress: () => null}
                    ]);
                    this.setState({ loader : false});
                });
        }},
        {text: 'Kapat', onPress: () => null},
      ]);
    }
    componentDidMount(){
      this.keyboardDidShowListener = Keyboard.addListener(
        'keyboardDidShow',
        this._keyboardDidShow.bind(this),
      );
      this.keyboardDidHideListener = Keyboard.addListener(
        'keyboardDidHide',
        this._keyboardDidHide.bind(this),
      );
		this.unsubscribe = NetInfo.addEventListener(state => {
			 this.handleConnectivityChange(state.isConnected);
        });
    }
    _keyboardDidShow() {
      this.setState({
        formyukari:0
      });
    }

    _keyboardDidHide() {
      this.setState({
        formyukari:1
      });
    }
	sendPerm = () =>{
		request(
		  Platform.select({
			android: PERMISSIONS.ANDROID.ACCESS_FINE_LOCATION,
			ios: PERMISSIONS.IOS.LOCATION_WHEN_IN_USE,
		  })
		).then(sonuc =>{
			if(sonuc=="blocked"){
				 Alert.alert("Bilgilendirme","Uygulamayı konum izni olmadan kullanamazsınız. İzin vermek istiyor musunuz?",[
                      {text: 'Evet', onPress: () => GPSState.openSettings()},
                      {text: 'İptal', onPress: () => BackHandler.exitApp()}
                    ]);
			}
			else if(sonuc != "granted"){
				 Alert.alert("Bilgilendirme","Konum izni olmadan uygulamayı kullanamazsınız. Konum izni vermek istiyor musunuz?",[
                      {text: 'Evet', onPress: () => this.sendPerm()},
                      {text: 'İptal', onPress: () => BackHandler.exitApp()}
                    ]);
			}else{



				try{
			Geolocation.getCurrentPosition(
			  (position) => {
				this.setState({
				  latitude: position.coords.latitude,
				  longitude: position.coords.longitude,
				  error: null,
				  loader:false
				});
					  this.mylat = position.coords.latitude;
							  this.mylng = position.coords.longitude;
				this._startTimer = setInterval(() => {
						Geolocation.getCurrentPosition(
						  (position) => {
							  this.mylat = position.coords.latitude;
							  this.mylng = position.coords.longitude;
						  },
						  (error) => null,
						  { enableHighAccuracy: true}
						);



				  this.getMyApp();
				}, 10000);
				this.getMyApp();
			  },
			  (error) => {
				   console.log(error);
				  if(error.code == 5){
					  	  BackHandler.exitApp();
				  }else if(error.code == 3){
					  this.konumumual();
				  }


			},
			  { enableHighAccuracy: true},
			);
		  }catch(e){
			console.log(e);
		  }



			}
		});
	}
	locationSetting(){
		this.sendPerm();
	}
    componentWillMount() {
	 OneSignal.init("841a45f9-2721-47f3-8da7-ac589721231a");
		   OneSignal.addEventListener('ids', this.onIds.bind(this));
		   OneSignal.inFocusDisplaying(0);
		   this.locationSetting();


  }

     onIds(device) {
       console.log(device);

	     fetch(global.apiurl,
                          {
                              method: 'POST',
                              headers:
                                  {
                                      'Accept': 'application/json',
                                      'Content-Type': 'application/json',
                                  },
                              body: JSON.stringify(
                                  {
                                      p : 'start',
                                      s : 16,
                                      userid:global.userid,
                                      token:global.token,
                                      onekey:device.userId
                                  })
                          }).then((response) => response.json()).then((jr) =>
                      {

                      }).catch((error) =>
                      {

                      });


     }

  handleBackPress(){
    this.props.navigation.goBack()
  }
  acilDurum(){

	Alert.alert(
      'VipUpp',
      'Acil durum çağrısı yollamak istediğinize emin misiniz ? ',
      [
        {text: 'Hayır', onPress: () => console.warn('NO Pressed'), style: 'cancel'},
        {text: 'Evet', onPress: () => this.acildurumcagrigonder()},
      ]
    );
  }
	acildurumcagrigonder(){
		fetch(global.apiurl,
            {
                method: 'POST',
                headers:
                {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(
                {
                  p : 'start',
                  s : 18,
                  userid:global.userid,
                  token:global.token,
				  tip:0
                })

            }).then((response) => response.json()).then((jr) =>
            {
                Alert.alert("Bilgilendirme","Acil durum çağrısı başarıyla gönderilmiştir.",[
                  {text: 'Kapat', onPress: () => null}
                ]);

            }).catch((error) =>
            {
                Alert.alert("Bilgilendirme","Hata Oluştu: "+JSON.stringify(error),[
                  {text: 'Kapat', onPress: () => null}
                ]);
                this.setState({ loader : false});
            });
	}

  handleConnectivityChange = isConnected => {
      if (isConnected) {
        this.setState({ isConnected });
      } else {
        this.setState({ isConnected });
      }
    };
  componentWillUnmount(){
	   this.unsubscribe();
    try{clearInterval(this._startTimer);}catch(e){console.log(e);}
    try{GPSState.removeListener();}catch(e){console.log(e);}
    try{BackHandler.removeEventListener('hardwareBackPress', this.handleBackPress)}catch(e){console.log(e);}
    try{OneSignal.removeEventListener('ids', this.onIds);}catch(e){console.log(e);}
  }
    onValueChange(value) {
        this.setState({
            selected: value
        });
    }
    getOdeme = ()=>
    {
      if (this.state.odemetipisecilen=="")
      {
        Alert.alert("Bilgilendirme","Ödeme türünü seçiniz.",[
            {text: 'Kapat', onPress: () => null}
        ]);
      }
      else
      {
        if (this.state.odemetipisecilen==1)
        {
          this.getOdemeAsama1();
        }
        else
        {
          this.setState({
            odemeForm:true
          });
        }
      }
    };
	getOdemeAsama1 = ()=>{
		 Alert.alert("Bilgilendirme","Seçilen araca özel rezervasyon yapmayı onaylıyor musunuz?",[
              {text: 'İstiyorum', onPress: () => {
                      fetch(global.apiurl,
                          {
                              method: 'POST',
                              headers:
                                  {
                                      'Accept': 'application/json',
                                      'Content-Type': 'application/json',
                                  },
                              body: JSON.stringify(
                                  {
                                      p : 'start',
                                      s : 19,
                                      userid:global.userid,
                                      token:global.token,
                                      fromlat:this.state.neredenlat,
                                      fromlng:this.state.neredenlng,
                                      fromtext:this.state.neredentext,
                                      tolat:this.state.nereyelat,
                                      tolng:this.state.nereyelng,
                                      totext:this.state.nereyetext,
                                      cartype:this.state.secilenAracTipi,
                                      km:(parseFloat(this.state.aradakimesafe)).toFixed(1),
                                      mylat:this.state.latitude,
                                      mylng:this.state.longitude,
                                      odeme:this.state.selected,
                                      odemetipi:this.state.kartno?"1":"0",
                                      money:(parseFloat(this.state.kmbasiucret)).toFixed(1),
                                      secilenTarih:this.state.secilenTarih,
                                      secilenSaat:this.state.saatSecilen
                                  })
                          }).then((response) => response.json()).then((jr) =>
                      {
                          if(jr["status"] == 1)
                          {
                            Alert.alert("Bilgilendirme",jr.message,[
                                {text: 'Kapat', onPress: () => {this.props.navigation.goBack()}}
                            ]);
                          }
                          else
                          {
                              Alert.alert("Bilgilendirme",jr.message,[
                                  {text: 'Kapat', onPress: () => null}
                              ]);
                          }
                      }).catch((error) =>
                      {
                          Alert.alert("Bilgilendirme","Hata Oluştu: "+JSON.stringify(error),[
                              {text: 'Kapat', onPress: () => null}
                          ]);
                          this.setState({ loader : false});
                      });
                  }},
              {text: 'İptal', onPress: () => null},
          ]);
	}
  odemeyap()
  {
    if (this.state.kartno=="")
    {
      Alert.alert("Bilgilendirme",'Kart numarasını giriniz.',[
          {text: 'Kapat', onPress: () => null}
      ]);
    }
    else if(this.state.kartay=="")
    {
      Alert.alert("Bilgilendirme",'Kart numarasının son kullanma ayını giriniz.',[
          {text: 'Kapat', onPress: () => null}
      ]);
    }
    else if(this.state.kartyil=="")
    {
      Alert.alert("Bilgilendirme",'Kart numarasının son kullanma yılını giriniz.',[
          {text: 'Kapat', onPress: () => null}
      ]);
    }
    else if(this.state.cvv=="")
    {
      Alert.alert("Bilgilendirme",'Kart numarasının ccv numarasını giriniz.',[
          {text: 'Kapat', onPress: () => null}
      ]);
    }
    else
    {
      fetch('http://www.vipupp.com/akbank/index.php', {
          method: 'POST',
          headers: {Accept: 'application/json','Content-Type': 'application/json',},
          body: JSON.stringify({
            odemeyap:"",
            kartno:this.state.kartno,
            cvv:this.state.cvv,
            kartyil:this.state.kartyil,
            kartay:this.state.kartay,
            toplamucret:this.state.toplamucret,
            karttipi:1
          }),
          }).then((response) => response.json())
          .then((rs) =>
          {
            if (rs.result==1)
            {
              Alert.alert("Bilgilendirme",'Ödemeniz Başarıyla Alınmıştır.',[
                  {text: 'Kapat', onPress: () => {
                    this.getOdemeAsama1();
                    this.setState({
                      odemeForm:false
                    });
                  }}
              ]);
            }
            else
            {
              Alert.alert("Bilgilendirme",'Ödeme esnasında hata oluştu lütfen bilgilerinizi kontrol edin.',[
                  {text: 'Kapat', onPress: () => null}
              ]);
            }
          })
          .catch((error) => {
            alert("Hata");
          });
    }
  }
  render() {

    return (
      <Container style={styles.anasayfaGenel1}>
      {
      this.state.loader?
        <View style={styles.indicator}>
        <Spinner color='green' />
        </View>:null
      }
      {
        !this.state.isConnected?<MiniOfflineSign />:null
      }

      <View style={styles.headerview}>
        <Header transparent style={styles.header, {backgroundColor:'#fff'}} androidStatusBarColor='#ffffff'>
          <Left style={{ flex: 1 }}>
            <Button style={styles.headeringeribtn} transparent onPress={() => this.props.navigation.goBack()}>
              <Icon style={styles.headeringeriicon} name="arrow-back" />
            </Button>
          </Left>
          <Body style={styles.logoOrtala}>
            <Image source={logo} style={styles.logo} resizeMode="contain" ></Image>
          </Body>
          <Right style={{ flex: 1 }}>
          </Right>
          </Header>
      </View>
		{
			//this.openSearchModal()
		}

    <Modal
                    style={[styles.modal, styles.modal3]}
                    ref={"modal1"}
                    position={"center"}
                    backButtonClose={true}
                >
                    <ScrollView contentContainerStyle={{flex: 1, justifyContent: "center", alignItems: "center"}}>
                        <Thumbnail style={{marginBottom: 10}} large
                                   source={this.state.driveravatar ? {uri: global.sunucu+"app/data/img/"+this.state.driveravatar} : {uri: global.sunucu + "app/data/img/noavatar.png"}}/>
                        <Text style={{
                            fontSize: 18
                        }}>{this.state.drivername}</Text>
                        <Text style={{
                            marginBottom: 10,
                            fontSize: 17
                        }}>{this.state.drivercarplate ? this.state.drivercarplate.split("-").join(" ") : ""}</Text>
                        <Text style={{
                            textAlign: 'center',
                            alignSelf: 'center',
                            marginBottom: 15,
                            fontSize: 18
                        }}>Seyahatinizi 1 ile 5
                            arasında puanlayabilirsiniz</Text>
                        <StarRating
                            disabled={false}
                            maxStars={5}
                            starSize={25}
                            rating={this.state.starCount}
                            emptyStarColor={'#CDD0D2'}
                            fullStarColor={'#DD7C39'}
                            style={{marginTop: 10, alignSelf: 'flex-end'}}
                            selectedStar={(rating) => this.onStarRatingPress(rating)}
                        />
                        <Textarea rowSpan={3}
                                  onChangeText={(text) => this.setState({yorum: text})}
                                  style={{width: '100%', marginTop: 7}} bordered
                                  placeholder="Yorumunuz"/>
                        <Button success onPress={() => {
                            this.seyahatPuanla();
                        }} style={{
                            backgroundColor: '#DD7C39', width: '100%',
                            textAlign: 'center',
                            alignSelf: 'center',
                            justifyContent: 'center',
                            marginTop: 15
                        }}><Text style={{
                            fontSize: 15,
                        }}>Puanla</Text></Button>
                    </ScrollView>
                </Modal>

  {!this.state.loader?
    <Content>
      {!this.state.seyehatlistesi?
        <View style={styless.inputview}>
          <Item style={this.state.neredentext && this.state.nereyetext? styless.adresarasecim : styless.adresara}>
            <Icon style={styless.adresaraicon}  type="FontAwesome" name='map-marker' />
            <TouchableOpacity activeOpacity={1} style={styles.touchable} onPress={() => this.props.navigation.navigate("haritaSecim", {veriIsle:this.openSearchModal,myKonum:this.myKonum})}>
              <Input style={styless.adresarainput} disabled placeholder='Nereden' value={this.state.neredentext}/>
            </TouchableOpacity>
          </Item>
        </View>:null
      }
      {
        !this.state.seyehatlistesi?
        <View style={styless.inputview2adim2}>
          <Item style={styless.nereyearaadim2}>
            <Icon style={styless.adresnereyeicon} type="FontAwesome"  name='send-o' />
            <TouchableOpacity activeOpacity={1} style={styles.touchable} onPress={() => this.props.navigation.navigate("haritaSecim", {veriIsle:this.openSearchModalNereye,myKonum:this.myKonum1})}>
              <Input style={styless.adresnereyeinput} disabled placeholder='Nereye' value={this.state.nereyetext}/>
            </TouchableOpacity>
          </Item>
        </View>:null
      }
      {
        !this.state.seyehatlistesi?
        <View style={styless.inputview3adim3}>
          <Item style={styless.nereyearaadim3}>
            <Icon style={styless.adresnereyeicon} type="FontAwesome"  name='calendar' />
            {
            this.state.tarihOpen && ( <DateTimePicker
                testID="dateTimePicker"
                value={new Date()}
                mode={'date'}
                is24Hour={true}
                display="default"
                minimumDate={new Date()}
                onChange={(this.tarihDegistir)}
              />)
            }
            <TouchableOpacity activeOpacity={1} style={[styles.touchable,{height: 50,justifyContent: 'center',paddingLeft: 5}]} onPress={() => {this.setState({tarihOpen:true})}}>
              <Text>{this.state.secilenTarih?this.state.secilenTarih:"Tarih"}</Text>
            </TouchableOpacity>
          </Item>
        </View>:null
      }
      {
        !this.state.seyehatlistesi?
        <View style={styless.inputview4adim4}>
          <Item style={styless.nereyearaadim4}>
            <Icon style={styless.adresnereyeicon} type="FontAwesome"  name='calendar' />
            {
            this.state.saatOpen && ( <DateTimePicker
                testID="dateTimePicker"
                value={new Date()}
                mode={'time'}
                is24Hour={true}
                display="default"
                onChange={(this.saatDegistir)}
              />)
            }
            <TouchableOpacity activeOpacity={1} style={[styles.touchable,{height: 50,justifyContent: 'center',paddingLeft: 5}]} onPress={() => {this.setState({saatOpen:true})}}>
              <Text>{this.state.saatSecilen?this.state.saatSecilen:"Saat"}</Text>
            </TouchableOpacity>
          </Item>
        </View>:null
      }

                                  <MapView
								  ref={(f)=>this.mapRef=f}
                    style={styless.map}
                    zoomEnabled = {true}
                    initialRegion={{
                        latitude: this.state.latitude,
                        longitude: this.state.longitude,
                        latitudeDelta: this.state.latdelta,
                        longitudeDelta: this.state.lngdelta
                    }}
                >
                <Marker
                coordinate={{latitude: this.state.latitude,
                         longitude: this.state.longitude}}
      image={require('../../../assets/konumum.png')}
    />
    {
      this.state.haritapois?
      this.state.haritapois.map(function(mark, i){
        return (<MapView.Marker
        coordinate={{latitude: parseFloat(mark.carlat),
                 longitude: parseFloat(mark.carlng)}}
        image={require("../../../assets/marker.png")}
        />
      )
    }):this.state.aracpois.map(function(mark,i){
    return (<MapView.Marker
            coordinate={{latitude: parseFloat(mark.carlat),
                     longitude: parseFloat(mark.carlng)}}
            image={require("../../../assets/marker.png")}
            />
          )
    })
    }
    {
      this.state.neredentext?
      <MapView.Marker
                coordinate={{latitude: this.state.neredenlat,
                         longitude: this.state.neredenlng}}
      image={require('../../../assets/mapnereden.png')}
    />:null
    }
    {
      this.state.nereyetext?
      <MapView.Marker
                coordinate={{latitude: this.state.nereyelat,
                         longitude: this.state.nereyelng}}
      image={require('../../../assets/mapnereye.png')}
    />:null
    }
{
  this.state.neredentext && this.state.nereyetext?

  <MapViewDirections
    origin={{ latitude: this.state.neredenlat, longitude: this.state.neredenlng }}
    destination={{ latitude: this.state.nereyelat, longitude: this.state.nereyelng }}
    apikey='AIzaSyAqc2J59eq2rLYhgnAlqIitylenG3NOy9k'
    strokeWidth={7}
    strokeColor="#F39200"
    onReady={(result) => {
        //alert(Result.distance);
      console.log(result);
            this.setState({aradakimesafe:parseFloat(result.distance).toFixed(1), arasikacdk:(result.duration).toFixed(1)+"dk"})
    }}
    onError={(errorMessage) => {
      this.setState({neredenlat:null, nereyelat:null, neredentext:null, nereyetext:null});
        Alert.alert("Belirtilen noktalar arası ulaşım sağlanamadı");
    }}
/>
  :null
}


                </MapView>

  {this.state.error ? <Text>Error: {this.state.error}</Text> : null}
  {(!this.state.neredentext || !this.state.nereyetext) && !this.state.seyehatlistesi?
<View style={styles.adim2btn}>
<Button transparent onPress={()=>this.konumumual()} style={styles.adim2btnbtn}>
            <Icon style={styles.adim2btnicon1} type="FontAwesome" name='compass' />
        </Button>
</View>
:null
}
{
    1==2 && (!this.state.neredentext || !this.state.nereyetext) && !this.state.seyehatlistesi?
  <View style={styless.inputview2}>
  <Item style={styless.nereyeara}>
              <Icon style={styless.adresnereyeicon}  name='send-outline' />
              <TouchableOpacity
              activeOpacity={1}
             style={styles.touchable}
             onPress={() => this.openSearchModalNereye()}
           >
           <Input style={styless.adresnereyeinput} disabled placeholder='Nereye' value={this.state.nereyetext}/>
           </TouchableOpacity>
           </Item>
            </View>:null
}

      </Content>
:null}

{ this.state.neredentext && this.state.nereyetext && this.state.secilenTarih && this.state.saatSecilen && !this.state.asama2gec && !this.state.secimasama?
<View style={ styles.asamaaracsecview }>

<ScrollView horizontal={true} style={styles.asamaracscroll} showsHorizontalScrollIndicator={false}>
  {
    this.state.araclistesi.map(function(user, i){
      return <View style={styles.asaaracsecinview}>
      <TouchableOpacity
          style={{alignItems:"center",alignSelf:"center",justifyContent:"center"}}
      activeOpacity={1}
        onPress={this.getArac.bind(this, user.id)}
       >
        <Image
           style={{width:(deviceWidth / 3)-40, height: (deviceWidth / 3)-40}}
           source={user.secilmis?{uri:global.sunucu+"app/data/img/"+user.resimhover}:{uri:global.sunucu+"app/data/img/"+user.resim}}
         />
        <Text
            numberOfLines={1}
            ellipsizeMode={"tail"}
          style={{
          backgroundColor:user.secilmis == true?'#F39200':'#CDD0D2',
          padding:3,
          borderRadius:3,
          color:'#fff',
          marginTop:10,
              maxWidth:(deviceWidth / 3)-30
        }}>{user.isim}</Text>
        </TouchableOpacity>
      </View>
     })
   }
</ScrollView>
<View style={styles.asaaracsecinviewinview}>
  <Button onPress={this.getAsama2.bind(this)} style={{width:deviceWidth - 30,justifyContent: 'center',backgroundColor:"#F39200"}}>
    <Text>Devam Et</Text>
  </Button>
</View>
</View>
:null}
          {
              this.state.secimasama && !this.state.odemeForm?
                  <View style={styles.asama2getiraracview}>
					  { /*<Text>Lütfen ödeme yönteminizi seçin:</Text>
                      <Picker
                          note
                          mode="dropdown"
                          style={{  }}
                          selectedValue={this.state.selected}
                          onValueChange={this.onValueChange.bind(this)}
                      >
                          <Picker.Item label="Seçiniz" value="" />
                          <Picker.Item label="Nakit" value="1" />
                          <Picker.Item label="Kredi Kartı" value="2" />
                      </Picker>*/}
                      <View style={styles.asama2getirarackisibilgiview}>
                          <View style={styles.asama2getirarackisibilgiviewelem,{flex:0.3}}>
                              <Text style={{color:'#CDD0D2', paddingBottom:10}}>Max Kişi</Text>
                              <Text>{this.state.arackisisayi}</Text>
                          </View>

                          <View style={styles.asama2getirarackisibilgiviewelem,{flex:0.3,alignItems: 'center',justifyContent: 'center'}}>
                              <Text style={{color:'#CDD0D2', paddingBottom:10}}>Mesafe</Text>
                              <Text>{(this.state.aradakimesafe)} KM</Text>
                          </View>

                          <View style={styles.asama2getirarackisibilgiviewelem,{flex:0.4,alignItems: 'flex-end',justifyContent: 'flex-end'}}>
                              <Text style={{color:'#CDD0D2', paddingBottom:10}}>Ücret</Text>
                              <Text>{this.state.toplamucret}₺</Text>
                          </View>
                      </View>
                      <View style={styles.asama2getirarackisibilgiviewtipisecin}>
                      <View style={styles.pickerdiv}>
                        <Picker style={{height: 42,color:"#959595",fontSize:8}} selectedValue={this.state.odemetipisecilen} onValueChange={(itemValue, itemIndex) =>this.setState({odemetipisecilen:itemValue})}>
                          <Picker.Item color="#959595" label="Ödeme Yöntemini Seçin" value="0" />
                          <Picker.Item color="#959595" label="Nakit" value="1" />
                          <Picker.Item color="#959595" label="Kredi Karti" value="2" />
                        </Picker>
                      </View>
                      </View>
                      <Button onPress={this.getOdeme.bind(this)} style={{width:deviceWidth - 60, marginTop:15,justifyContent: 'center',backgroundColor:"#F39200"}}>
                          <Text>Devam Et</Text>
                      </Button>
                  </View>
                  :null
          }
		  {
              this.state.odemeForm?
                  <View  style={[this.state.formyukari==1?styles.asama2getiraracview:styles.asama2getiraracview2]}>
                      <View style={{flex:1}}>
                          <Form style={styles.kayitform}>
                							<Item  style={styles.inputitem}>
                							  <Icon style={styles.destekicons} type="Ionicons" active name="card" />
                							  <Input style={styles.inputdestek} placeholder="Kart Numarası*" maxLength={16} keyboardType="numeric" onChangeText={ (text) => this.setState({ kartno: text })}/>
                							</Item>
                							<View style={{flexDirection:"row", flex:1}}>
                							<Item style={[styles.inputitem,{flex:0.5}]}>
                							  <Icon style={styles.destekicons} type="Ionicons" active name="calendar" />
                                <Picker note mode="dropdown" selectedValue={this.state.kartay} onValueChange={ (text) => this.setState({ kartay: text })}>
                                  <Picker.Item label={"Ay"} value="" />
                                  {
                                    ["01","02","03","04","05","06","07","08","09","10","11","12"].map((v)=><Picker.Item label={v} value={v} />)
                                  }
                                </Picker>
                							</Item>
                							<Item style={[styles.inputitem,{flex:0.5}]}>
                							  <Icon style={styles.destekicons} type="Ionicons" active name="calendar" />
                                <Picker note mode="dropdown" selectedValue={this.state.kartyil} onValueChange={ (text) => this.setState({ kartyil: text })}>
                                  <Picker.Item label={"Yil"} value="" />
                                  {
                                    ["20","21","22","23","24","25","26","27","28","29","30","31","32"].map((v)=><Picker.Item label={"20"+v} value={v} />)
                                  }
                                </Picker>
                							</Item>
                							</View>
                							<Item style={styles.inputitem}>
                							  <Icon style={styles.destekicons} type="Ionicons" active name="lock" />
                							  <Input style={styles.inputdestek} placeholder="CVV*" maxLength={3} onChangeText={ (text) => this.setState({ cvv: text })} />
                							</Item>

                						  </Form>
                      </View>
                      <Button onPress={() => this.odemeyap()} style={{width:deviceWidth - 60, marginTop:15,justifyContent: 'center',backgroundColor:"#F39200"}}>
                          <Text>Öde ve Devam Et</Text>
                      </Button>
                      <Button onPress={() => this.setState({odemeForm:false})} style={{width:deviceWidth - 60, marginTop:15,justifyContent: 'center',backgroundColor:"red"}}>
                          <Text>Geri Dön</Text>
                      </Button>
                  </View>
                  :null
          }

{
  this.state.asama2gec || (this.state.seyehatlistesi && this.state.seyehatlistesi.status!=2)?
<View style={styles.asama2getiraracview}>
    <MarqueeText
        style={{ fontSize: 16,color:"#F39200",marginBottom:5 }}
        duration={parseInt(this.marqueetext.length*100)}
        marqueeOnStart
        loop
        marqueeDelay={2000}
        marqueeResetDelay={2000}
    >
        {this.marqueetext}
    </MarqueeText>
{this.state.asama3gec || (this.state.seyehatlistesi && this.state.seyehatlistesi.status == 1)?
  <><View style={styles.asama2getirarackisiview}>
      <View style={styles.asama2getirarackisiview1}>
        <Image source={{uri:global.sunucu+"app/data/img/"+this.state.seyehatlistesi.driver.avatar}}
        style={{ width: 75, height:75, borderRadius:75}}
        />
      </View>
      <View style={styles.asama2getirarackisiview2}>
        <Text style={{marginBottom:5}}>{this.state.seyehatlistesi.driver.name}</Text>
        <StarRating
         disabled={true}
         maxStars={5}
         rating={this.state.seyehatlistesi.driver.rating}
         starSize={20}
         containerStyle={{width:100}}
         emptyStarColor={'#CDD0D2'}
         fullStarColor={'#FFB900'}
       />
       <Text style={{marginTop:5}}>{this.state.seyehatlistesi.driver.car_plate}</Text>
      </View>
      <View style={styles.asama2getirarackisiview3,{flex:1, flexDirection:'column'}}>
        <Button transparent style={{alignSelf:'flex-end', height:38}} onPress={this.driverCall.bind(this)}>
          <Icon name="call" type="MaterialIcons" style={{fontSize:30, color:'#6BBD44'}}/>
        </Button>
        <Button transparent style={{alignSelf:'flex-end', height:38}} onPress={this.messageSend.bind(this,this.state.seyehatlistesi.driver.id)}>
          <Icon name="message" type="MaterialIcons" style={{fontSize:30, color:'#6BBD44'}}/>
        </Button>
      </View>


  </View>
    <View style={{marginBottom: 15}}><Text style={{marginBottom: 5}}>Sürücünün uzaklığı dk olarak: <Text style={{fontWeight: 'bold'}}>{this.state.surucuDakika}</Text></Text>
    <Text>Sürücünün uzaklığı km olarak: <Text style={{fontWeight: 'bold'}}>{this.state.surucuMesafe}</Text></Text>
  </View>
  </>
:<View style={styles.asama2getirarackisiview}>
<View style={styles.asama2getirarackisiview1}>
  <Spinner color='green' style={{ width: 75, height:75, borderRadius:75}}/>
</View>
<View style={styles.asama2getirarackisiview2a}>
  <Text style={{ alignSelf:'flex-start'}}>Konumunuza yönlendirilecek araç bekleniyor</Text>
</View>
<View style={styles.asama2getirarackisiview3}>
  <Button danger style={{height:35, }} onPress={()=>{this.iptalEtTrasfer()}}><Text>İptal Et</Text></Button>
</View>
</View>
}
  <View style={styles.asama2getirarackisibilgiview}>
    <View style={styles.asama2getirarackisibilgiviewelem,{flex:0.3}}>
      <Text style={{color:'#CDD0D2', paddingBottom:10}}>Max Kişi</Text>
      <Text>{this.state.arackisisayi}</Text>
    </View>

    <View style={styles.asama2getirarackisibilgiviewelem,{flex:0.33}}>
      <Text style={{color:'#CDD0D2', paddingBottom:10}}>Ödeme Türü</Text>
      <Text>{!this.state.selected?"-":this.state.selected=="1"?"Nakit":"Kredi Kartı"}</Text>
    </View>

    <View style={styles.asama2getirarackisibilgiviewelem,{flex:0.30}}>
      <Text style={{color:'#CDD0D2', paddingBottom:10}}>Mesafe</Text>
      <Text>{(this.state.aradakimesafe)} KM</Text>
    </View>

    <View style={styles.asama2getirarackisibilgiviewelem,{flex:0.17}}>
      <Text style={{color:'#CDD0D2', paddingBottom:10}}>Ücret</Text>
      <Text>{this.state.toplamucret}₺</Text>
    </View>
  </View>
</View>
  :null
}
      </Container>
    );
  }
}

AppRegistry.registerComponent('MAP', () => MAP);

export default Anasayfa;
