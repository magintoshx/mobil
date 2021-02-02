import React, { Component } from "react";
import {Image, View,Alert,AsyncStorage} from "react-native";
import {Container, Header, Title, Content, Button, Left, Right, Body, Icon, H1, Accordion, Form, Item, Input, Text, Spinner} from "native-base";
import styles from "./styles";
import {NavigationActions, StackActions} from 'react-navigation';

const logo = require("../../../assets/logo.png");


class smsonay extends Component {
  constructor(props) {
      super(props);
      this.state = {
        loader:true, smsonaykodu:null
      };
  }
  componentDidMount(){
    if(!global.smsuserid){
      Alert.alert("Onaylanacak hesap bilgisine ulaşılamadı");
      this.props.navigation.navigate("home");
    }else{
      this.setState({loader:false});
    }
  }

  smsonayla(){
    if(!this.state.smsonaykodu){
     Alert.alert("Bilgilendirme","Lütfen sms onay kodunuzu giriniz",[
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
                  s : 4,
                  userid:global.smsuserid,
                  smskod:this.state.smsonaykodu
                })

            }).then((response) => response.json()).then(async (jr) =>
            {
              if(jr["status"] == 1){
				   await AsyncStorage.setItem('token', jr.token);
					await AsyncStorage.setItem('userid', jr.userid.toString());
					global.token = jr.token;
					global.userid = jr.userid.toString();
					
					let resetToHome = StackActions.reset({
                        index: 0,
                        actions: [NavigationActions.navigate({routeName: 'Drawer'})],
                        key: null,
                    });
                    this.props.navigation.dispatch(resetToHome);

                Alert.alert("Bilgilendirme",jr.message,[
                  {text: 'Kapat', onPress: () => null}
                ]);

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
  tekrarsmsonay(){
      Alert.alert("Bilgilendirme","Telefonunuza tekar sms kodu gönderilsin mi?",[
         {text: 'Gönder', onPress: () => {
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
                       s : 3,
                       userid:global.smsuserid,
                       smskod:this.state.smsonaykodu
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
         }},
         {text: 'Kapat', onPress: () => null}
       ]);
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
            <Text style={{paddingBottom:15}}>Hesabınızı onaylayabilmeniz için telefon numaranıza gönderilen onay kodunu aşağıdaki alana giriniz.</Text>
              <Item style={styles.inputitem}>
                <Icon style={styles.loginiconssifre} type="Ionicons" active name="lock" />
                <Input placeholder="Onay Kodu" maxLength={6} keyboardType="numeric" onChangeText={ (text) => this.setState({ smsonaykodu: text }) }/>
              </Item>

            </Form>

            <Button style={styles.girisbtn} onPress={()=>this.smsonayla()}>
              <Text style={styles.txt} uppercase={false}>Hesabımı Onayla</Text>
            </Button>
            <Button style={[styles.blockbtn,styles.blackBtn]} info onPress={()=>this.tekrarsmsonay()}>
              <Text style={styles.txt} uppercase={false}>Tekrar Kod Gönder</Text>
            </Button>
          </View>
        </Content>
      </Container>
    );
  }
}

export default smsonay;
