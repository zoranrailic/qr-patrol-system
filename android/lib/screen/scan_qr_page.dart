import 'dart:async';
import 'dart:convert';

import 'package:attendancewithqr/database/db_helper.dart';
import 'package:attendancewithqr/model/settings.dart';
import 'package:attendancewithqr/screen/main_menu_page.dart';
import 'package:attendancewithqr/utils/strings.dart';
import 'package:auto_size_text/auto_size_text.dart';
import 'package:barcode_scan2/barcode_scan2.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:rflutter_alert/rflutter_alert.dart';

import '../utils/utils.dart';

class ScanQrPage extends StatefulWidget {
  const ScanQrPage({super.key});

  @override
  ScanQrPageState createState() => ScanQrPageState();
}

class ScanQrPageState extends State<ScanQrPage> {
  DbHelper dbHelper = DbHelper();
  Utils utils = Utils();
  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();
  String _barcode = "";
  late Settings settings;
  String _isAlreadyDoSettings = 'loading';

  Future<void> scan() async {
    try {
      final barcode = await BarcodeScanner.scan();

      /// The value of Qr Code
      /// Return the json data
      /// We need replaceAll because Json from web use single-quote ({' '}) not double-quote ({" "})
      final newJsonData = barcode.rawContent.replaceAll("'", '"');
      final data = jsonDecode(newJsonData);

      /// Check the type of barcode
      if (data['url'] != null && data['key'] != null) {
        /// Decode the json data form QR
        final getUrl = data['url'].toString();
        final getKey = data['key'].toString();

        /// Set the url and key
        settings = Settings(url: getUrl, key: getKey);

        /// Insert the settings
        insertSettings(settings);
      } else {
        utils.showAlertDialog(
          formatBarcodeWrong,
          "Error",
          AlertType.error,
          _scaffoldKey,
          isAnyButton: false,
        );
      }
    } on PlatformException catch (e) {
      setState(() {
        _isAlreadyDoSettings = 'no';
      });
      if (e.code == BarcodeScanner.cameraAccessDenied) {
        _barcode = barcodePermissionCamClose;
        utils.showAlertDialog(
          _barcode,
          "Warning",
          AlertType.warning,
          _scaffoldKey,
          isAnyButton: false,
        );
      } else {
        _barcode = '$barcodeUnknownError $e';
        utils.showAlertDialog(
          _barcode,
          "Error",
          AlertType.error,
          _scaffoldKey,
          isAnyButton: false,
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
  Future<void> insertSettings(Settings object) async {
    await dbHelper.newSettings(object);
    setState(() {
      _isAlreadyDoSettings = 'yes';
      goToMainMenu();
    });
  }

  Future<void> getSettings() async {
    final checking = await dbHelper.countSettings();
    checking! > 0 ? _isAlreadyDoSettings = 'yes' : _isAlreadyDoSettings = 'no';
    setState(() {});
    if (mounted) {
      goToMainMenu();
    }
  }

  /// Init for the first time
  @override
  void initState() {
    super.initState();
    splashScreen();
  }

  /// Show splash scree with time duration
  Future<Timer> splashScreen() async {
    const duration = Duration(seconds: 5);
    return Timer(duration, () {
      getSettings();
    });
  }

  /// Got to main menu after scanning the QR or if user scanned the QR.
  void goToMainMenu() {
    if (_isAlreadyDoSettings == 'yes') {
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (context) => const MainMenuPage()),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    /// Check if user already do settings
    if (_isAlreadyDoSettings == 'no') {
      return MaterialApp(
        home: Scaffold(
          backgroundColor: const Color(0xFF07074E),
          key: _scaffoldKey,
          body: Container(
            margin: const EdgeInsets.all(40.0),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: <Widget>[
                const Image(
                  image: AssetImage('images/logo.png'),
                ),
                const SizedBox(
                  height: 10.0,
                ),
                AutoSizeText(
                  settingWelcomeFitle,
                  style: GoogleFonts.quicksand(
                    fontSize: 20.0,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(
                  height: 20.0,
                ),
                AutoSizeText(
                  settingDesc,
                  style: GoogleFonts.quicksand(
                    fontSize: 12.0,
                    color: Colors.grey[300],
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(
                  height: 40.0,
                ),

                /// ignore: deprecated_member_use
                ElevatedButton(
                  onPressed: () => scan(),
                  style: ElevatedButton.styleFrom(
                    foregroundColor: const Color(0xFF07074E),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(18.0),
                    ),
                  ),
                  child: Text(
                    buttonScan,
                    style: GoogleFonts.quicksand(
                      color: Colors.black,
                      fontSize: 15.0,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      );
    }

    return Container(
      color: const Color(0xFF07074E),
      child: const Center(
        child: Image(
          image: AssetImage('images/logo.png'),
        ),
      ),
    );
  }
}
