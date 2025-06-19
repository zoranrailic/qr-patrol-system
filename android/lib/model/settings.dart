import 'dart:convert';

String settingsToJson(Settings data) {
  final dyn = data.toJson();
  return json.encode(dyn);
}

class Settings {
  int? id;
  String? url;
  String? key;

  Settings({this.id, this.url, this.key});

  factory Settings.fromJson(Map<String, dynamic> json) => Settings(
        id: int.parse(json["id"].toString()),
        key: json["key"].toString(),
        url: json["url"].toString(),
      );

  Map<String, dynamic> toJson() => {
        "id": id,
        "url": url,
        "key": key,
      };
}
