import React, { Component } from "react";
import { ImageBackground, View, StatusBar,Image,TouchableOpacity, Alert} from "react-native";
import { Container, Spinner, Header, Title, Content, Text, Button, Icon, Footer, FooterTab, Left, Right, Body,Badge, Form,Item, Input,Textarea, Label} from "native-base";
import ImagePicker from 'react-native-image-crop-picker';

import styles from "./styles";

const logo = require("../../../assets/logo.png");
const launchscreenBg = require("../../../assets/launchscreen-bg.png");
const launchscreenLogo = require("../../../assets/logo-kitchen-sink.png");
class profilduzenle extends Component {
  constructor(props) {
      super(props);
      this.state = {
        uavatar: null,
        username: null,
        loader:true,
        name:null,
        phone:null,
        mail:null,
        sifre:null,
        sifret:null,
        esifre:null
      };
    }
  componentWillMount(){
	this.verileriCek();
  }
verileriCek(){
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
                s : 4,
                userid:global.userid,
                token:global.token
              })

          }).then((response) => response.json()).then((jr) =>
          {
            if(jr["status"] == 1){
              this.setState({
                avatar:jr.message.user.avatar?jr.message.user.avatar:"noavatar.png",
                username:jr.message.user.name,
                name:jr.message.user.name,
                phone:jr.message.user.phone,
                mail:jr.message.user.mail,
              })
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
  guncelleProfil(){
    this.setState({loader:true});
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
                s : 5,
                userid:global.userid,
                token:global.token,
                name: this.state.name,
                mail:this.state.mail,
                phone:this.state.phone,
                pass:this.state.sifre?this.state.sifre:"",
                repass:this.state.sifret?this.state.sifret:"",
                oldpass:this.state.esifre?this.state.esifre:"",
              })

          }).then((response) => response.json()).then((jr) =>
          {
            if(jr["status"] == 1){
              Alert.alert("Bilgilendirme",jr.message,[
                {text: 'Kapat', onPress: () => null}
              ]);
              this.props.navigation.goBack();
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
  avataryukle = (avatar) =>{
    this.setState({loader:true});
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
                s : 7,
                userid:global.userid,
                token:global.token,
                image: avatar
              })

          }).then((response) => response.json()).then((jr) =>
          {
            Alert.alert("Bilgilendirme",jr.message,[
              {text: 'Kapat', onPress: () => {
				  this.setState({ loader : false});
				  this.verileriCek();
			  }}
            ]);

          }).catch((error) =>
          {
              Alert.alert("Bilgilendirme","Hata Oluştu: "+JSON.stringify(error),[
                {text: 'Kapat', onPress: () => null}
              ]);
              this.setState({ loader : false});
          });
  }
  avatarIslemler(){
	   Alert.alert("Bilgilendirme","Görsel seçmek istediğiniz kaynağı seçin",[
                {text: 'Galeri', onPress: () => {
					ImagePicker.openPicker({
  width: 300,
  height: 300,
  cropping: true,
  includeBase64:true
}).then(image => {
	this.avataryukle(image.data);
});

//
				}},
                {text: 'Kamera', onPress: () => {
					ImagePicker.openCamera({
  width: 300,
  height: 300,
  cropping: true,
  includeBase64:true
}).then(image => {
 	this.avataryukle(image.data);

});
				}},
                {text: 'İptal', onPress: () => null}
              ]);
  }
  render() {
    return (
      <Container style={styles.anasayfaGenel, {backgroundColor:'#fff'}}>
      {
        this.state.loader?
        <View style={styles.indicator}>
        <Spinner color='green' />
        </View>:null
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
        <Right style={{marginTop:-10}}>

        </Right>
      </Header>
      </View>
        <Content>
            <View style={{marginTop:110, marginLeft:10}}>
            <Text style={styles.pagetitle}>Profil Düzenle</Text>
            <View style={{width:'100%', justifyContent:'center', alignItems:'center'}}>
			<TouchableOpacity onPress={()=>this.avatarIslemler()}>
			              <Image source={{uri:global.sunucu+"app/data/img/"+this.state.avatar}} style={{ width:80, height:80, borderRadius:80, borderWidth:1, borderColor:'#000', padding:5}} />
			</TouchableOpacity>

              <Label>{this.state.username}</Label>
            </View>
              <View style={styles.destekform}>
              <Form style={styles.kayitform}>
                <Item  style={styles.inputitem}>
                  <Icon style={styles.loginicons} type="Ionicons" active name="person" />
                  <Input placeholder="Ad, Soyad" autoCapitalize="none" value={this.state.name} onChangeText={(text) => this.setState({ name: text })}/>
                </Item>
                <Item  style={styles.inputitem}>
                  <Icon style={styles.loginicons} type="Ionicons" active name="call" />
                  <Input placeholder="Telefon Numarası" maxLength={10} keyboardType="phone-pad" onChangeText={ (text) => this.setState({ phone: text })}  value={this.state.phone}/>
                </Item>
                <Item  style={styles.inputitem}>
                  <Icon style={styles.loginicons} type="Ionicons" active name="mail" />
                  <Input placeholder="E-Mail" autoCapitalize="none" value={this.state.mail}  onChangeText={ (text) => this.setState({ mail: text })} />
                </Item>
                <Item style={styles.inputitem}>
                  <Icon style={styles.loginiconssifre} type="Ionicons" active name="lock" />
                  <Input placeholder="Kullanımdaki Şifreniz" secureTextEntry={true}  onChangeText={ (text) => this.setState({ esifre: text })}/>
                </Item>
                <Item style={styles.inputitem}>
                  <Icon style={styles.loginiconssifre} type="Ionicons" active name="lock" />
                  <Input placeholder="Yeni Şifre" secureTextEntry={true}  onChangeText={ (text) => this.setState({ sifre: text })}/>
                </Item>
                <Item style={styles.inputitem}>
                  <Icon style={styles.loginiconssifre} type="Ionicons" active name="lock" />
                  <Input placeholder="Yeni Şifre Tekrar" secureTextEntry={true}  onChangeText={ (text) => this.setState({ sifret: text })}/>
                </Item>
                  <Button style={{backgroundColor:"#F39200",flex:1, width:'100%', marginTop:10, marginBottom:25,justifyContent: 'center'}} onPress={()=>this.guncelleProfil()}>
                    <Text>Güncelle</Text>
                  </Button>
                </Form>
              </View>
            </View>
        </Content>
        </Container>
    );
  }
}

export default profilduzenle;
