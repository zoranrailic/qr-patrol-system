import 'dart:async';
import 'dart:convert';

import 'package:attendancewithqr/database/db_helper.dart';
import 'package:attendancewithqr/model/settings.dart';
import 'package:attendancewithqr/utils/strings.dart';
import 'package:barcode_scan2/barcode_scan2.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:rflutter_alert/rflutter_alert.dart';

import '../utils/utils.dart';

class SettingPage extends StatefulWidget {
  const SettingPage({super.key});

  @override
  SettingPageState createState() => SettingPageState();
}

class SettingPageState extends State<SettingPage> {
  DbHelper dbHelper = DbHelper();
  Utils utils = Utils();
  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();
  String _barcode = "";
  late Settings settings;

  Future<void> scan() async {
    try {
      final barcode = await BarcodeScanner.scan();

      /// The value of Qr Code
      /// Return the json data
      /// We need replaceAll because Json from web use single-quote ({' '}) not double-quote ({" "})
      final newJsonData = barcode.rawContent.replaceAll("'", '"');
      final data = jsonDecode(newJsonData);

      if (data['url'] != null && data['key'] != null) {
        /// Decode the json data form QR
        final getUrl = data['url'].toString();
        final getKey = data['key'].toString();

        /// Set the url and key
        settings = Settings(id: 1, url: getUrl, key: getKey);

        /// Update the settings
        updateSettings(settings);
      } else {
        utils.showAlertDialog(
          formatBarcodeWrong,
          "Error",
          AlertType.error,
          _scaffoldKey,
          isAnyButton: true,
        );
      }
    } on PlatformException catch (e) {
      if (e.code == BarcodeScanner.cameraAccessDenied) {
        _barcode = cameraPermission;
        utils.showAlertDialog(
          _barcode,
          "Warning",
          AlertType.warning,
          _scaffoldKey,
          isAnyButton: true,
        );
      } else {
        _barcode = '$barcodeUnknownError $e';
        utils.showAlertDialog(
          _barcode,
          "Error",
          AlertType.error,
          _scaffoldKey,
          isAnyButton: true,
        );
      }
    } catch (e) {
      _barcode = '$barcodeUnknownError : $e';
      if (kDebugMode) {
        print(_barcode);
      }
    }
  }

  /// Insert the URL and KEY
  Future<void> updateSettings(Settings object) async {
    await dbHelper.updateSettings(object);
    goToMainMenu();
  }

  void goToMainMenu() {
    Navigator.of(context).pop();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      key: _scaffoldKey,
      appBar: AppBar(
        title: const Text(settingTitle),
      ),
      body: Container(
        margin: const EdgeInsets.all(20.0),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: <Widget>[
            const SizedBox(
              width: double.infinity,
              height: 100.0,
              child: Image(
                image: AssetImage('images/logo.png'),
              ),
            ),
            const SizedBox(
              height: 40.0,
            ),

            /// ignore: deprecated_member_use
            ElevatedButton(
              onPressed: () => scan(),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF07074E),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(18.0),
                ),
              ),
              child: Text(
                buttonScan,
                style:
                    GoogleFonts.quicksand(color: Colors.white, fontSize: 12.0),
              ),
            ),
            const SizedBox(
              height: 10.0,
            ),
            Text(
              settingInfo,
              style: GoogleFonts.quicksand(color: Colors.grey, fontSize: 12.0),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}
