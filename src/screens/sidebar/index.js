import React, { Component } from "react";
import { Image, Alert, AsyncStorage } from "react-native";
import {Content,Text,List,ListItem,Icon,Container,Left,Right,Badge} from "native-base";
import styles from "./style";
const drawerImage = require("../../../assets/logo.png");
import {NavigationActions, StackActions} from 'react-navigation';

const datas = [
  {
    name: "Rezervasyon Oluştur",
    route: "rezervasyon",
    icon: "notifications",
    bg: "#C5F442"
  },
  {
    name: "Profilim",
    route: "profil",
    icon: "person",
    bg: "#C5F442",
  },
  {
    name: "Mesajlarım",
    route: "mesajlar",
    icon: "mail",
    bg: "#C5F442",
  },
  {
    name: "Seyahat Geçmişi",
    route: "seyahatgecmis",
    icon: "refresh",
    bg: "#C5F442"
  },
  {
    name: "Bildirimler",
    route: "bildirim",
    icon: "notifications",
    bg: "#C5F442"
  },
  {
    name: "Yardım & Destek",
    route: "destek",
    icon: "help-circle",
    bg: "#C5F442"
  },
  {
    name: "Çıkış",
    route: "Anatomy",
    cikismi:true,
    icon: "exit",
    bg: "#C5F442"
  },
];

class SideBar extends Component {
   oturumukapat(){
     Alert.alert(
       'Onay İsteği',
       'Oturumunuzu kapatmak istediğinize emin misiniz?',
       [
         {text: 'Hayır', onPress: () => console.log('Cancel Pressed'), style: 'cancel'},
         {text: 'Evet', onPress: async () =>{
           await AsyncStorage.removeItem('token');
           await AsyncStorage.removeItem('userid');
		   let resetToHome = StackActions.reset({
                        index: 0,
                        actions: [NavigationActions.navigate({routeName: 'Home'})],
                        key: null,
                    });
                    this.props.navigation.dispatch(resetToHome);
         }},
       ],
       { cancelable: false }
     );
   }
  constructor(props) {
    super(props);
    this.state = {
      shadowOffsetWidth: 1,
      shadowRadius: 4
    };
  }

  render() {
    return (
      <Container>
        <Content
          bounces={false}
          style={{ flex: 1, backgroundColor: "#fff", top:0 }}
        >
          <Image square style={styles.drawerImage} source={drawerImage} resizeMode="contain"/>

          <List
            dataArray={datas}
            renderRow={data =>
              <ListItem
                button
                noBorder
                onPress={data.cikismi?this.oturumukapat.bind(this):() => this.props.navigation.navigate(data.route)}
              >
                  <Icon
                    active
                    name={data.icon}
                    style={{ color: "#F39200", fontSize: 30, width: 30 }}
                  />
                  <Text style={styles.text}>
                    {data.name}
                  </Text>

              </ListItem>}
          />
        </Content>
      </Container>
    );
  }
}

export default SideBar;
