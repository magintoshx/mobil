import React from "react";
import Setup from "./src/boot/setup";

export default class App extends React.Component {
  render() {
    console.disableYellowBox = true;
    global.apiurl="http://vipupp.com/index.php";
    global.sunucu = "http://vipupp.com/";
    return <Setup />;
  }
}
