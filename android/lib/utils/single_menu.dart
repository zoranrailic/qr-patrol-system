import 'package:auto_size_text/auto_size_text.dart';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class SingleMenu extends StatelessWidget {
  final IconData icon;
  final String menuName;
  final String? textDesc;
  final Color? color;
  final Function()? action;

  const SingleMenu({
    super.key,
    required this.icon,
    required this.menuName,
    this.textDesc,
    this.color,
    this.action,
  });

  @override
  Widget build(BuildContext context) {
    return Material(
      child: GestureDetector(
        onTap: action,
        child: Container(
          margin: const EdgeInsets.only(top: 10.0, bottom: 5.0),
          width: MediaQuery.of(context).size.width * 0.9,
          height: 100,
          decoration: BoxDecoration(
            color: color,
            borderRadius: BorderRadius.circular(8),
          ),
          child: Row(
            children: [
              Padding(
                padding: const EdgeInsetsDirectional.fromSTEB(20, 0, 0, 0),
                child: Icon(
                  icon,
                  size: 36.0,
                  color: Colors.white,
                ),
              ),
              Expanded(
                child: Padding(
                  padding: const EdgeInsetsDirectional.fromSTEB(12, 20, 12, 0),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        menuName,
                        textAlign: TextAlign.start,
                        style: GoogleFonts.quicksand(
                          color: Colors.white,
                          fontSize: 20.0,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(
                        height: 5.0,
                      ),
                      Expanded(
                        child: Padding(
                          padding:
                              const EdgeInsetsDirectional.fromSTEB(0, 0, 0, 8),
                          child: AutoSizeText(
                            textDesc!,
                            style: GoogleFonts.quicksand(
                              color: const Color(0xB4FFFFFF),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
