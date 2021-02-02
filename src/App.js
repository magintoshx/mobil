    import React from "react";
import { Root } from "native-base";
import { AsyncStorage } from "react-native";
import {createDrawerNavigator} from 'react-navigation-drawer';
import {createStackNavigator} from 'react-navigation-stack';
import {createAppContainer} from 'react-navigation';

import Header from "./screens/Header/";


import kayit from "./screens/home/kayit";
import sifreunuttum from "./screens/home/sifreunuttum";
import sifreunuttumadim2 from "./screens/home/sifreunuttumadim2";
import smsonay from "./screens/home/smsonay";

import Anasayfa from "./screens/anasayfa/index";
import profil from "./screens/anasayfa/profil";
import seyahatgecmis from "./screens/anasayfa/seyahatgecmis";
import bildirim from "./screens/anasayfa/bildirim";
import destek from "./screens/anasayfa/destek";
import mesajlar from "./screens/anasayfa/mesajlar";
import mesajlas from "./screens/anasayfa/mesajlas";
import profilduzenle from "./screens/anasayfa/profilduzenle";

import haritaSecim from "./screens/anasayfa/haritaSecim";
import rezervasyon from "./screens/anasayfa/rezervasyon";

import Home from "./screens/home/";
import start from "./screens/home/start";

import SideBar from "./screens/sidebar";

const Drawer = createDrawerNavigator(
  {
    Anasayfa: { screen: Anasayfa },
  },
  {
    initialRouteName: "Anasayfa",
    contentOptions: {
      activeTintColor: "#e91e63"
    },
    contentComponent: props => <SideBar {...props} />
  }
);

const AppNavigator = createStackNavigator(
  {
    Home: { screen: Home },
    Anasayfa: { screen: Anasayfa },
    Drawer: { screen: Drawer },
    kayit: { screen: kayit },
    sifreunuttum: { screen: sifreunuttum },
    sifreunuttumadim2: { screen: sifreunuttumadim2 },
    smsonay: { screen: smsonay },
    profil: { screen: profil },
    bildirim: { screen: bildirim },
    destek: { screen: destek },
    seyahatgecmis: { screen: seyahatgecmis },
    mesajlar: { screen: mesajlar },
    mesajlas: { screen: mesajlas },
    profilduzenle: { screen: profilduzenle },
    haritaSecim: { screen: haritaSecim },
    rezervasyon: { screen: rezervasyon },
    start: { screen: start },
  },
  {
    initialRouteName: "start",
    headerMode: "none"
  }
);
const AppContainer = createAppContainer(AppNavigator);

export default () =>
  <Root>
    <AppContainer />
  </Root>;
