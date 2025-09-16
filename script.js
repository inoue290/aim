let resultCounts = {};
let chartInstance = null;
let tokenClient;

const CLIENT_ID = '773727067609-lmgihr4fq73ph1o27co2su9ed7rhqn31.apps.googleusercontent.com'; // ← ここをGoogle Cloud Consoleで発行したクライアントIDに置き換える

window.onload = () => {
  addMachine();

  google.accounts.id.initialize({
    client_id: CLIENT_ID,
    callback: (response) => {
      console.log("ID Token:", response.credential);
      alert("✅ Googleログイン成功");
    }
  });

  google.accounts.id.prompt();

  tokenClient = google.accounts.oauth2.initTokenClient({
    client_id: CLIENT_ID,
    scope: 'https://www.googleapis.com/auth/drive.file',
    callback: (tokenResponse) => {
      console.log("Access Token:", tokenResponse.access_token);
      uploadToDrive(tokenResponse.access_token);
    }
  });
};

function getAccessTokenAndUpload() {
  tokenClient.requestAccessToken();
}

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
  const allValues = [];

  machineDivs.forEach(div => {
    const machineId = div.querySelector('.machine-id').value.trim();
    const rotations = [...div.querySelectorAll('.rotation')];

    const validValues = rotations
      .map(r => parseInt(r.value.trim()))
      .filter(v => !isNaN(v));

    allValues.push(...validValues.slice(0, 5));
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

  // 円グラフのサイズを画面に合わせてレスポンシブに描画
  chartInstance = new Chart(ctx, {
    type: 'pie',
    data: {
      labels: labels.map((label, idx) => `${label} (${percents[idx]}%)`),
      datasets: [{
        data: values,
        backgroundColor: labels.map(() => `hsl(${Math.random() * 360}, 70%, 70%)`)
      }]
    },
    options: {
      responsive: true, // レスポンシブ対応
      plugins: {
        legend: {
          position: 'top',
        },
        tooltip: {
          callbacks: {
            label: (tooltipItem) => {
              return `${tooltipItem.label}: ${tooltipItem.raw} 回`;
            }
          }
        }
      }
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

function uploadToDrive(accessToken) {
  const csvContent = generateCSV();
  const blob = new Blob([csvContent], { type: 'text/csv' });

  const metadata = {
    name: '当選回転数分析.csv',
    mimeType: 'text/csv',
    parents: ['root']
  };

  const form = new FormData();
  form.append('metadata', new Blob([JSON.stringify(metadata)], { type: 'application/json' }));
  form.append('file', blob);

  fetch('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&fields=id', {
    method: 'POST',
    headers: new Headers({ Authorization: 'Bearer ' + accessToken }),
    body: form
  }).then(res => res.json())
    .then(val => {
      alert('✅ アップロード成功！ファイルID: ' + val.id);
    })
    .catch(err => {
      console.error('❌ アップロード失敗', err);
      alert('❌ Driveアップロードに失敗しました');
    });
}

