import React, { Component } from "react";
import {Image, View, Alert} from "react-native";
import {Container,Spinner, Header, Title, Content, Button, Left, Right, Body, Icon, H1, Accordion, Form, Item, Input, ListItem, CheckBox, Text} from "native-base";
import styles from "./styles";

const logo = require("../../../assets/logo.png");
import {NavigationActions, StackActions} from 'react-navigation';

class sifreunuttumadim2 extends Component {
  constructor(props) {
      super(props);
      this.state = {
        loader:false, pass:null, repass:null, code:null
      };
  }

sifredegistir(){
  if(!global.sifresmsphone){
    Alert.alert("Bilgilendirme","Telefon numaranıza ulaşılamadı",[
      {text: 'Kapat', onPress: () => null}
    ]);
  }else if(!this.state.pass || !this.state.repass || !this.state.code){
    Alert.alert("Bilgilendirme","Lütfen tüm alanları doldurunuz",[
      {text: 'Kapat', onPress: () => null}
    ]);
  }
  else{
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
                s : 6,
                phone:global.sifresmsphone,
                pass:this.state.pass,
                repass:this.state.repass,
                code:this.state.code
              })

          }).then((response) => response.json()).then((jr) =>
          {
			  console.log(jr);
            if(jr["status"] == "1"){
              Alert.alert("Bilgilendirme",jr.message,[
                {text: 'Kapat', onPress: () => null}
              ]);


					let resetToHome = StackActions.reset({
                        index: 0,
                        actions: [NavigationActions.navigate({routeName: 'Drawer'})],
                        key: null,
                    });
                    this.props.navigation.dispatch(resetToHome);
					
            }else{
              Alert.alert("Bilgilendirme",jr.message,[
                {text: 'Kapat', onPress: () => null}
              ]);
            }
           this.setState({ loader : false });
          }).catch((error) =>
          {
			  console.log(error);
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
            <Title></Title>
          </Body>
          <Right />
        </Header>
        <Content padder style={{ backgroundColor: "white" }}>
          <View style={styles.kayitContainer}>
            <Image source={logo} style={styles.logo} resizeMode="contain" ></Image>
            <Form style={styles.kayitform}>
            <Text style={{fontSize:20, marginBottom:20, justifyContent:'center', alignSelf:'center'}}>{global.sifresmsphone}</Text>
            <Item  style={styles.inputitem}>
              <Icon style={styles.loginicons} type="Ionicons" active name="time" />
              <Input placeholder="Onay Kodunuz" maxLength={6} keyboardType="numeric" onChangeText={ (text) => this.setState({ code: text })}/>
            </Item>
              <Item  style={styles.inputitem}>
                <Icon style={styles.loginicons} type="Ionicons" active name="lock" />
                <Input placeholder="Yeni Şifreniz" secureTextEntry={true} onChangeText={ (text) => this.setState({ pass: text })}/>
              </Item>
              <Item  style={styles.inputitem}>
                <Icon style={styles.loginicons} type="Ionicons" active name="lock" />
                <Input placeholder="Yeni Şifreniz Tekrar" secureTextEntry={true} onChangeText={ (text) => this.setState({ repass: text })}/>
              </Item>
            </Form>
            <Button style={styles.girisbtn} onPress={()=>this.sifredegistir()}>
              <Text style={styles.txt} uppercase={false}>Şifremi Değiştir</Text>
            </Button>
          </View>
        </Content>
      </Container>
    );
  }
}

export default sifreunuttumadim2;
