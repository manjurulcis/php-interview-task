class Travel
{
private $travels;

public function __construct()
{
$this->travels = $this->fetchTravels();
}

private function fetchTravels()
{
$json = file_get_contents('https://5f27781bf5d27e001612e057.mockapi.io/webprovise/travels');
return json_decode($json, true);
}

public function getTravelCostsByCompanyId()
{
$costs = [];
foreach ($this->travels as $travel) {
if (!isset($costs[$travel['companyId']])) {
$costs[$travel['companyId']] = 0;
}
$costs[$travel['companyId']] += $travel['price'];
}
return $costs;
}
}

class Company
{
private $companies;
private $travelCosts;

public function __construct($travelCosts)
{
$this->travelCosts = $travelCosts;
$this->companies = $this->fetchCompanies();
$this->calculateCosts();
}

private function fetchCompanies()
{
$json = file_get_contents('https://5f27781bf5d27e001612e057.mockapi.io/webprovise/companies');
return json_decode($json, true);
}

private function calculateCosts()
{
foreach ($this->companies as &$company) {
$company['cost'] = $this->travelCosts[$company['id']] ?? 0;
}
}

public function buildCompanyTree($parentId = "0")
{
$tree = [];
foreach ($this->companies as &$company) {
if ($company['parentId'] === $parentId) {
$children = $this->buildCompanyTree($company['id']);
if ($children) {
$company['children'] = $children;
$company['cost'] += array_sum(array_column($children, 'cost'));
} else {
$company['children'] = [];
}
$tree[] = $company;
}
}
return $tree;
}
}

class TestScript
{
public function execute()
{
$start = microtime(true);

$travel = new Travel();
$travelCosts = $travel->getTravelCostsByCompanyId();

$company = new Company($travelCosts);
$companyTree = $company->buildCompanyTree();

echo json_encode($companyTree, JSON_PRETTY_PRINT);
echo 'Total time: ' . (microtime(true) - $start);
}
}

(new TestScript())->execute();