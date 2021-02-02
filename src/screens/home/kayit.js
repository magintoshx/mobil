import React, { Component } from "react";
import {Image, View, Alert} from "react-native";
import {Container, Header, Title, Content, Button, Left, Right, Body, Icon, H1, Accordion, Form, Item, Input, ListItem, CheckBox, Text,Spinner} from "native-base";
import styles from "./styles";

const logo = require("../../../assets/logo.png");

class kayit extends Component {
  constructor(props) {
      super(props);
      this.state = {
        loader:false, phone:null,
        name:null,
        mail:null,
        sifre:null,
        sifret:null,
        kosullar:true,
        editable:false
      };
  }
  componentDidMount(){
    setTimeout(() => { this.setState({ editable: true }) }, 100);
  }
  getirKosullar(){
    this.setState({ loader: true});
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
                p : 'nologin',
                s : 10
              })

          }).then((response) => response.json()).then((jr) =>
          {
              Alert.alert("Bilgilendirme",jr.message,[
                {text: 'Kapat', onPress: () => null}
              ]);
           this.setState({ loader : false });
          }).catch((error) =>
          {
              Alert.alert("Bilgilendirme","Hata Oluştu: "+JSON.stringify(error),[
                {text: 'Kapat', onPress: () => null}
              ]);
              this.setState({ loader : false});
          });
  }
  kayitol(){
    if(!this.state.name || !this.state.mail || !this.state.phone || !this.state.sifre || !this.state.sifret || !this.state.kosullar){
      Alert.alert("Bilgilendirme","Lütfen tüm alanları doldurunuz",[
        {text: 'Kapat', onPress: () => null}
      ]);
    }else{
      this.setState({ loader: true});
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
                  p : 'nologin',
                  s : 2,
                  phone:this.state.phone,
                  name:this.state.name,
                  mail:this.state.mail,
                  pass:this.state.sifre,
                  repass:this.state.sifret,
                  kosul:this.state.kosullar
                })

            }).then((response) => response.json()).then((jr) =>
            {
              if(jr["status"] == "1"){
                Alert.alert("Bilgilendirme",jr.message,[
                  {text: 'Kapat', onPress: () => null}
                ]);
                global.smsuserid = jr.userid;
                 this.props.navigation.navigate("smsonay");
              }else{
                Alert.alert("Bilgilendirme",jr.message,[
                  {text: 'Kapat', onPress: () => null}
                ]);
              }
             this.setState({ loader : false });
            }).catch((error) =>
            {
                Alert.alert("Bilgilendirme","Hata Oluştu: "+JSON.stringify(error),[
                  {text: 'Kapat', onPress: () => null}
                ]);
                this.setState({ loader : false});
            });
    }
  }
  render() {
    return (
      <Container>
      {
        this.state.loader?
<View style={styles.indicator}>
<Spinner color='green' />
</View>:null
}
        <Header barStyle="kayitbar" androidStatusBarColor='#ffffff'>
        <Left style={{ flex: 1 }}>
          <Button style={styles.headeringeribtn} transparent onPress={() => this.props.navigation.goBack()}>
            <Icon style={styles.headeringeriicon} name="arrow-back" />
          </Button>
        </Left>
          <Body>
            <Title>Kayıt Ol</Title>
          </Body>
          <Right />
        </Header>
        <Content padder style={{ backgroundColor: "white" }}>
          <View style={styles.kayitContainer}>
            <Image source={logo} style={styles.logo} resizeMode="contain" ></Image>
            <Form style={styles.kayitform}>
              <Item  style={styles.inputitem}>
                <Icon style={styles.loginicons} type="Ionicons" active name="person" />
                <Input placeholder="Ad, Soyad" autoCapitalize="none" onChangeText={(text) => this.setState({ name: text })}/>
              </Item>
              <Item  style={styles.inputitem}>
                <Icon style={styles.loginicons} type="Ionicons" active name="call" />
                <Input placeholder="Telefon Numarası(535*******)" maxLength={10} keyboardType="phone-pad" onChangeText={ (text) => this.setState({ phone: text })} />
              </Item>
              <Item  style={styles.inputitem}>
                <Icon style={styles.loginicons} type="Ionicons" active name="mail" />
                <Input editable={this.state.editable} placeholder="E-Mail" autoCapitalize="none" keyboardType="email-address" onChangeText={ (text) => this.setState({ mail: text })} />
              </Item>
              <Item style={styles.inputitem}>
                <Icon style={styles.loginiconssifre} type="Ionicons" active name="lock" />
                <Input Text="password" placeholder="Şifre" secureTextEntry={true}  onChangeText={ (text) => this.setState({ sifre: text })}/>
              </Item>
              <Item style={styles.inputitem}>
                <Icon style={styles.loginiconssifre} type="Ionicons" active name="lock" />
                <Input Text="password" placeholder="Şifre Tekrar" secureTextEntry={true}  onChangeText={ (text) => this.setState({ sifret: text })}/>
              </Item>
            </Form>
            <View style={{flexDirection: 'row'}}>
              <View style={styles.kosuldiv}>
                <ListItem button style={{height:14,paddingTop:10,borderBottomWidth:0,marginLeft:0}}>
                  <CheckBox style={{backgroundColor: this.state.kosullar?'#F39200':'#ccc',borderColor: '#E6E7EB'}} checked={this.state.kosullar} onPress={() => this.setState({ kosullar: !this.state.kosullar })} />
                  <Body>
                    <Text onPress={()=>{this.getirKosullar()}}>Kullanım Koşullarını Kabul Ediyorum</Text>
                  </Body>
                </ListItem>
              </View>
            </View>
            <Button style={styles.girisbtn} onPress={()=>this.kayitol()}>
              <Text style={styles.txt} uppercase={false}>Kayıt Ol</Text>
            </Button>
            <View style={{marginBottom: 20}}></View>
          </View>
        </Content>
      </Container>
    );
  }
}

export default kayit;
