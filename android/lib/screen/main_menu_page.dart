import 'package:attendancewithqr/screen/attendance_page.dart';
import 'package:attendancewithqr/screen/report_page.dart';
import 'package:attendancewithqr/screen/setting_page.dart';
import 'package:attendancewithqr/utils/single_menu.dart';
import 'package:attendancewithqr/utils/strings.dart';
import 'package:auto_size_text/auto_size_text.dart';
import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import 'package:geolocator/geolocator.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:permission_handler/permission_handler.dart';

import 'about_page.dart';

class MainMenuPage extends StatelessWidget {
  const MainMenuPage({super.key});

  @override
  Widget build(BuildContext context) {
    return const Menu();
  }
}

class Menu extends StatefulWidget {
  const Menu({super.key});

  @override
  MenuState createState() => MenuState();
}

class MenuState extends State<Menu> {
  @override
  void initState() {
    _getPermission();
    super.initState();
  }

  Future<void> _getPermission() async {
    getPermissionAttendance();
  }

  Future<void> getPermissionAttendance() async {
    await [
      Permission.camera,
      Permission.location,
      Permission.locationWhenInUse,
    ].request().then((value) {
      _determinePosition();
    });
  }

  Future<Position> _determinePosition() async {
    bool serviceEnabled;
    LocationPermission permission;

    /// Test if location services are enabled.
    serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) {
      getSnackBar('Location services are disabled.');
    }

    permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
      if (permission == LocationPermission.denied) {
        getSnackBar('Location permissions are denied');
      }
    }

    if (permission == LocationPermission.deniedForever) {
      getSnackBar(
        'Location permissions are permanently denied, we cannot request permissions.',
      );
    }

    return Geolocator.getCurrentPosition();
  }

  /// Show snackBar
  ScaffoldFeatureController<SnackBar, SnackBarClosedReason> getSnackBar(
    String messageSnackBar,
  ) {
    return ScaffoldMessenger.of(context)
        .showSnackBar(SnackBar(content: Text(messageSnackBar)));
  }

  final ButtonStyle flatButtonStyle = TextButton.styleFrom(
    foregroundColor: Colors.black87,
    minimumSize: const Size(88, 36),
    padding: const EdgeInsets.symmetric(horizontal: 16.0),
    shape: const RoundedRectangleBorder(
      borderRadius: BorderRadius.all(Radius.circular(2.0)),
    ),
  );

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SingleChildScrollView(
        child: Container(
          margin: const EdgeInsets.only(bottom: 20.0),
          child: Column(
            children: [
              Container(
                width: double.infinity,
                height: 180.0,
                color: const Color(0xFF07074E),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Padding(
                      padding: EdgeInsets.only(top: 20.0), // Dodaje 20px padding iznad
                      child: Image(
                        image: AssetImage('images/logo.png'),
                      ),
                    ),
                    const SizedBox(
                      width: 10.0,
                    ),
                    Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        AutoSizeText(
                          mainMenuHi,
                          style: GoogleFonts.quicksand(
                            fontSize: 20.0,
                            color: Colors.white,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(
                          height: 10.0,
                        ),
                        AutoSizeText(
                          mainMenuTitle,
                          style: GoogleFonts.quicksand(
                            fontSize: 15.0,
                            color: const Color(0xB4FFFFFF),
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              SingleMenu(
                icon: FontAwesomeIcons.clock,
                menuName: mainMenuCheckIn,
                textDesc: mainMenuCheckInDec,
                color: Colors.blue,
                action: () => Navigator.of(context).push(
                  MaterialPageRoute(
                    builder: (context) => const AttendancePage(
                      query: 'in',
                      title: mainMenuCheckInTitle,
                    ),
                  ),
                ),
              ),
              SingleMenu(
                icon: FontAwesomeIcons.rightFromBracket,
                menuName: mainMenuCheckOut,
                textDesc: mainMenuCheckOutDec,
                color: Colors.teal,
                action: () => Navigator.of(context).push(
                  MaterialPageRoute(
                    builder: (context) => const AttendancePage(
                      query: 'out',
                      title: mainMenuCheckOutTitle,
                    ),
                  ),
                ),
              ),
              SingleMenu(
                icon: FontAwesomeIcons.gears,
                menuName: mainMenuSettings,
                textDesc: mainMenuSettingsDec,
                color: Colors.green,
                action: () => Navigator.of(context).push(
                  MaterialPageRoute(builder: (context) => const SettingPage()),
                ),
              ),
              SingleMenu(
                icon: FontAwesomeIcons.calendar,
                menuName: mainMenuReport,
                textDesc: mainMenuReportDec,
                color: Colors.pinkAccent[700],
                action: () => Navigator.of(context).push(
                  MaterialPageRoute(builder: (context) => const ReportPage()),
                ),
              ),
              SingleMenu(
                icon: FontAwesomeIcons.userLarge,
                menuName: mainMenuAbout,
                textDesc: mainMenuAboutDec,
                color: Colors.purple,
                action: () => Navigator.of(context).push(
                  MaterialPageRoute(builder: (context) => const AboutPage()),
                ),
              ),
              SingleMenu(
                icon: FontAwesomeIcons.info,
                menuName: 'v 1.0',
                textDesc: mainMenuVersionDec,
                color: Colors.red[300],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
