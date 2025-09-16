let resultCounts = {};
let chartInstance = null;
let googleAccessToken = null;

function addMachine() {
  const container = document.getElementById('machineContainer');
  const index = container.children.length + 1;

  const div = document.createElement('div');
  div.className = 'machine-entry';
  div.innerHTML = `
    <label>台番号 ${index}</label>
    <input type="text" placeholder="例：A001" class="machine-id" />
    <label>回転数（最大5件）</label><br>
    <input type="number" class="rotation" placeholder="回転数1" />
    <input type="number" class="rotation" placeholder="回転数2" />
    <input type="number" class="rotation" placeholder="回転数3" />
    <input type="number" class="rotation" placeholder="回転数4" />
    <input type="number" class="rotation" placeholder="回転数5" />
  `;
  container.appendChild(div);
}

function analyze() {
  const machineDivs = document.querySelectorAll('.machine-entry');
  const machineData = {};
  const allValues = [];

  machineDivs.forEach(div => {
    const machineId = div.querySelector('.machine-id').value.trim();
    const rotations = [...div.querySelectorAll('.rotation')];
    if (!machineId) return;

    const validValues = rotations
      .map(r => parseInt(r.value.trim()))
      .filter(v => !isNaN(v));

    machineData[machineId] = validValues.slice(0, 5);
    allValues.push(...machineData[machineId]);
  });

  const maxRange = Math.max(...allValues, 0);
  const bins = Math.ceil(maxRange / 50);
  resultCounts = {};

  for (let i = 0; i < bins; i++) {
    resultCounts[i] = 0;
  }

  for (let num of allValues) {
    const binIndex = Math.floor(num / 50);
    resultCounts[binIndex]++;
  }

  drawPieChart(resultCounts, allValues.length);
}

function drawPieChart(data, totalCount) {
  const labels = Object.keys(data).map(i => `${i * 50}〜${(parseInt(i) + 1) * 50 - 1}G`);
  const values = Object.values(data);
  const percents = values.map(v => ((v / totalCount) * 100).toFixed(2));

  if (chartInstance) {
    chartInstance.destroy();
  }

  const ctx = document.getElementById('pieChart').getContext('2d');
  chartInstance = new Chart(ctx, {
    type: 'pie',
    data: {
      labels: labels.map((label, idx) => `${label} (${percents[idx]}%)`),
      datasets: [{
        data: values,
        backgroundColor: labels.map(() => `hsl(${Math.random() * 360}, 70%, 70%)`)
      }]
    }
  });
}

function generateCSV() {
  const csvRows = [['区分', '当選回数']];
  for (const [binIndex, count] of Object.entries(resultCounts)) {
    const rangeLabel = `${binIndex * 50}〜${(parseInt(binIndex) + 1) * 50 - 1}G`;
    csvRows.push([rangeLabel, count]);
  }
  return csvRows.map(e => e.join(",")).join("\n");
}

function downloadCSV() {
  const csvContent = generateCSV();
  const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.setAttribute("href", url);
  link.setAttribute("download", "当選回転数分析.csv");
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

// GIS Callback
function handleCredentialResponse(response) {
  const jwt = response.credential;
  console.log("JWT Token:", jwt);

  // Exchange JWT for access_token using OAuth2 token exchange
  fetch("https://oauth2.googleapis.com/tokeninfo?id_token=" + jwt)
    .then(res => res.json())
    .then(userInfo => {
      alert("✅ Googleログイン成功: " + userInfo.email);
    })
    .catch(err => {
      console.error("トークン検証失敗", err);
      alert("❌ ログイン失敗");
    });
}

function uploadToDrive() {
  const csvContent = generateCSV();
  const blob = new Blob([csvContent], { type: 'text/csv' });

  const file = new File([blob], "当選回転数分析.csv", {
    type: 'text/csv'
  });

  const picker = window.google.picker; // Google Picker APIが必要な場合、別途対応
  alert("⚠️ Driveアップロードは別途アクセストークンによる処理が必要です（サーバー側実装が必要）");
}
