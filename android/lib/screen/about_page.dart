import 'package:attendancewithqr/utils/strings.dart';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class AboutPage extends StatelessWidget {
  const AboutPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text(aboutTitle),
      ),
      body: Container(
        margin: const EdgeInsets.all(10.0),
        child: Column(
          children: [
            const Image(
              image: AssetImage('images/logo.png'),
            ),
            const SizedBox(
              height: 20.0,
            ),
            Center(
              child: Text(
                aboutAppName,
                textAlign: TextAlign.center,
                style: GoogleFonts.quicksand(
                    fontSize: 20.0, fontWeight: FontWeight.bold,),
              ),
            ),
            const SizedBox(
              height: 20.0,
            ),
            Text(
              aboutDeveloper,
              style: GoogleFonts.quicksand(fontSize: 13.0, color: Colors.grey),
            ),
            Text(
              aboutUrl,
              style: GoogleFonts.quicksand(fontSize: 13.0, color: Colors.grey),
            ),
            const SizedBox(
              height: 20.0,
            ),
            Text(
              aboutDesc,
              style: GoogleFonts.quicksand(fontSize: 15.0, color: Colors.grey),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}
