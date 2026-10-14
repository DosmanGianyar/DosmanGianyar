import '../models/school_regulation.dart';
import 'api_client.dart';

class RegulationService {
  RegulationService._();

  static Future<({List<RegulationGroup> groups, String? pdfUrl})> fetchAll() async {
    final body = await ApiClient.get('/school-regulations');
    final list = (body['regulations'] as List<dynamic>?) ?? [];
    final groups = list.map((e) => RegulationGroup.fromJson(e as Map<String, dynamic>)).toList();
    final pdfUrl = body['pdf_url'] as String?;
    return (groups: groups, pdfUrl: pdfUrl);
  }
}
