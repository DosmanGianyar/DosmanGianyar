import '../models/school_regulation.dart';
import 'api_client.dart';

class RegulationService {
  RegulationService._();

  static Future<({List<RegulationGroup> groups, List<UniformGuide> uniformGuides, String? pdfUrl})> fetchAll() async {
    final body = await ApiClient.get('/school-regulations');
    final list = (body['regulations'] as List<dynamic>?) ?? [];
    final groups = list.map((e) => RegulationGroup.fromJson(e as Map<String, dynamic>)).toList();
    
    final uniformsList = (body['uniform_guides'] as List<dynamic>?) ?? [];
    final uniformGuides = uniformsList.map((e) => UniformGuide.fromJson(e as Map<String, dynamic>)).toList();

    final pdfUrl = body['pdf_url'] as String?;
    return (groups: groups, uniformGuides: uniformGuides, pdfUrl: pdfUrl);
  }
}
