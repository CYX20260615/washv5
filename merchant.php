<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>洗鞋店商家后台</title>
    <style>
        * { box-sizing: border-box; font-family: system-ui, sans-serif; }
        body { background: #f0f2f5; padding: 20px; margin: 0; }
        .container { max-width: 1200px; margin: 0 auto; }
        .card { background: white; border-radius: 28px; padding: 24px; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        h2, h3 { margin-top: 0; }
        .stats-grid { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 24px; }
        .stat-card { background: #f8fafc; border-radius: 28px; padding: 16px; flex: 1; text-align: center; border-left: 4px solid #3b82f6; }
        .stat-number { font-size: 1.8rem; font-weight: bold; color: #1e2a3e; }
        .stat-label { color: #5b6e8c; margin-top: 8px; }
        .form-group { margin-bottom: 16px; }
        label { display: block; font-weight: 500; margin-bottom: 6px; }
        input, select { width: 100%; padding: 12px; border: 1px solid #cfdee9; border-radius: 28px; }
        button { padding: 12px 24px; border-radius: 40px; background: #3b82f6; color: white; font-weight: bold; border: none; cursor: pointer; margin-top: 8px; }
        .btn-secondary { background: #10b981; }
        .btn-danger { background: #ef4444; margin-top: 16px; }
        .shoes-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .shoes-table th, .shoes-table td { border: 1px solid #cfdee9; padding: 8px; text-align: left; }
        .shoes-table input { width: 100%; padding: 6px; }
        .order-tabs { display: flex; gap: 12px; margin-bottom: 20px; border-bottom: 1px solid #cfdee9; flex-wrap: wrap; }
        .tab { padding: 8px 16px; cursor: pointer; border-bottom: 2px solid transparent; }
        .tab.active { border-bottom-color: #3b82f6; font-weight: bold; }
        .order-item { background: #f8fafc; border-radius: 20px; padding: 16px; margin-bottom: 12px; border-left: 4px solid #3b82f6; }
        .order-photos { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 8px; }
        .order-photos img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
        .photo-upload { margin-top: 12px; }
        hr { margin: 20px 0; }
        .promo-select-group { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
        .promo-select-group select { flex: 1; }
        .promo-select-group input { flex: 2; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body>
<div class="container">
    <!-- 统计看板卡片 -->
    <div class="card">
        <h2>📊 订单统计</h2>
        <div class="stats-grid" id="statsPanel">
            <div class="stat-card">加载中...</div>
        </div>
    </div>

    <!-- 商家设置卡片 -->
    <div class="card">
        <h2>⚙️ 商家设置</h2>
        <div style="margin-bottom: 20px;">
            <label>可选鞋类清单（勾选启用并设置单价）</label>
            <table id="shoesTable" class="shoes-table">
                <thead><tr><th>启用</th><th>鞋类名称</th><th>单价(元)</th><th>操作</th></tr></thead>
                <tbody id="shoesTbody"></tbody>
            </table>
            <button type="button" id="addCustomBtn" class="btn-secondary" style="width:auto;">+ 添加自定义鞋类</button>
        </div>
        <hr>
        <h3>🎁 优惠规则</h3>
        <div class="form-group">
            <label>满额 (元)</label>
            <input type="number" id="full_amount" step="10" value="100">
        </div>
        <div class="form-group">
            <label>减额 (元)</label>
            <input type="number" id="reduce_amount" step="5" value="10">
        </div>
        <div class="form-group">
            <label>自定义活动说明（显示在客户下单页）</label>
            <div class="promo-select-group">
                <select id="promoTemplate">
                    <option value="">-- 选择模板 --</option>
                    <option value="新客立减5元">新客立减5元</option>
                    <option value="满100减10元">满100减10元</option>
                    <option value="转发朋友圈减5元">转发朋友圈减5元</option>
                    <option value="两人同行各减5元">两人同行各减5元</option>
                </select>
                <input type="text" id="custom_promo" placeholder="或手动输入活动说明">
            </div>
        </div>
        <button id="saveSettings">保存设置</button>
    </div>

    <!-- 订单管理卡片 -->
    <div class="card">
        <h2>📋 订单管理</h2>
        <div class="order-tabs" id="orderTabs">
            <div class="tab active" data-status="pending">待处理</div>
            <div class="tab" data-status="processing">处理中</div>
            <div class="tab" data-status="completed">已完成</div>
            <div class="tab" data-status="after_sale">售后中</div>
        </div>
        <div id="orderList"></div>
        <button id="clearOrders" class="btn-danger">清空所有订单</button>
    </div>
</div>

<script>
    const apiUrl = 'api.php';
    let orders = [];
    let shoesData = [];

    const presetShoes = [
        "运动鞋（成人）", "皮鞋（成人）", "特殊材质（麂皮/翻毛皮）", "靴子/棉鞋", "凉鞋/拖鞋",
        "童鞋（运动）", "童鞋（皮鞋）", "帆布鞋", "板鞋", "高跟鞋", "休闲鞋"
    ];

    function renderShoes() {
        const tbody = document.getElementById('shoesTbody');
        tbody.innerHTML = '';
        shoesData.forEach((shoe, idx) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="checkbox" class="enabled" data-idx="${idx}" ${shoe.enabled ? 'checked' : ''}></td>
                <td>${escapeHtml(shoe.name)}</td>
                <td><input type="number" step="1" class="price" value="${shoe.price}" data-idx="${idx}" ${!shoe.enabled ? 'disabled' : ''}></td>
                <td>${shoe.isCustom ? `<button class="delete-custom" data-idx="${idx}">删除</button>` : '—'}</td>
            `;
            tbody.appendChild(tr);
        });
        document.querySelectorAll('.enabled').forEach(cb => cb.addEventListener('change', (e) => {
            const idx = parseInt(e.target.dataset.idx);
            shoesData[idx].enabled = e.target.checked;
            const priceInput = e.target.closest('tr').querySelector('.price');
            priceInput.disabled = !e.target.checked;
            if (!e.target.checked) shoesData[idx].price = 0;
        }));
        document.querySelectorAll('.price').forEach(inp => inp.addEventListener('change', (e) => {
            const idx = parseInt(e.target.dataset.idx);
            shoesData[idx].price = parseFloat(e.target.value) || 0;
        }));
        document.querySelectorAll('.delete-custom').forEach(btn => btn.addEventListener('click', () => {
            const idx = parseInt(btn.dataset.idx);
            if (shoesData[idx].isCustom) {
                shoesData.splice(idx, 1);
                renderShoes();
            }
        }));
    }

    async function loadConfig() {
        try {
            const res = await axios.get(`${apiUrl}?action=getConfig`);
            const config = res.data;
            const stored = config.shoes || [];
            shoesData = presetShoes.map(name => {
                const found = stored.find(s => s.name === name);
                return { name, enabled: found ? found.enabled : false, price: found ? found.price : 0, isCustom: false };
            });
            const custom = stored.filter(s => !presetShoes.includes(s.name)).map(s => ({
                name: s.name, enabled: s.enabled, price: s.price, isCustom: true
            }));
            shoesData.push(...custom);
            renderShoes();
            document.getElementById('full_amount').value = config.coupon.full_amount;
            document.getElementById('reduce_amount').value = config.coupon.reduce_amount;
            document.getElementById('custom_promo').value = config.custom_promo || '';
        } catch (err) {
            console.error(err);
            shoesData = presetShoes.map(name => ({ name, enabled: false, price: 0, isCustom: false }));
            renderShoes();
        }
    }

    async function saveConfig() {
        const config = {
            shoes: shoesData.map(s => ({ name: s.name, enabled: s.enabled, price: s.price, isCustom: s.isCustom })),
            coupon: {
                enabled: true,
                full_amount: parseFloat(document.getElementById('full_amount').value),
                reduce_amount: parseFloat(document.getElementById('reduce_amount').value)
            },
            custom_promo: document.getElementById('custom_promo').value
        };
        try {
            const res = await axios.post(`${apiUrl}?action=saveConfig`, config);
            alert(res.data.success ? '保存成功' : '保存失败');
        } catch { alert('保存失败'); }
    }

    // 统计函数
    function updateStats() {
        const today = new Date().toISOString().slice(0,10);
        const currentMonth = today.slice(0,7);
        let todayIncome = 0, monthIncome = 0, totalOrders = orders.length, completedOrders = 0;
        orders.forEach(order => {
            if (order.status === 'completed') {
                completedOrders++;
                const orderDate = order.createdAt.slice(0,10);
                if (orderDate === today) todayIncome += parseFloat(order.total);
                if (orderDate.slice(0,7) === currentMonth) monthIncome += parseFloat(order.total);
            }
        });
        const statsHtml = `
            <div class="stat-card"><div class="stat-number">¥${todayIncome.toFixed(2)}</div><div class="stat-label">今日收入</div></div>
            <div class="stat-card"><div class="stat-number">¥${monthIncome.toFixed(2)}</div><div class="stat-label">本月收入</div></div>
            <div class="stat-card"><div class="stat-number">${totalOrders}</div><div class="stat-label">总订单数</div></div>
            <div class="stat-card"><div class="stat-number">${completedOrders}</div><div class="stat-label">已完成订单</div></div>
        `;
        document.getElementById('statsPanel').innerHTML = statsHtml;
    }

    async function loadOrders() {
        const res = await axios.get(`${apiUrl}?action=getOrders`);
        orders = res.data;
        updateStats(); // 更新统计
        updateTabCounts();
        const activeStatus = document.querySelector('.tab.active').dataset.status;
        renderOrders(activeStatus);
    }

    function updateTabCounts() {
        const counts = { pending: 0, processing: 0, completed: 0, after_sale: 0 };
        orders.forEach(o => { if (counts[o.status] !== undefined) counts[o.status]++; });
        document.querySelectorAll('.tab').forEach(tab => {
            const status = tab.dataset.status;
            let label = '';
            if (status === 'pending') label = '待处理';
            else if (status === 'processing') label = '处理中';
            else if (status === 'completed') label = '已完成';
            else if (status === 'after_sale') label = '售后中';
            const cnt = counts[status];
            tab.textContent = cnt > 0 ? `${label} (${cnt})` : label;
        });
    }

    function renderOrders(status) {
        const filtered = orders.filter(o => o.status === status);
        const container = document.getElementById('orderList');
        if (!filtered.length) { container.innerHTML = '<p>暂无订单</p>'; return; }
        container.innerHTML = filtered.map(order => `
            <div class="order-item" data-id="${order.id}">
                <div><strong>${escapeHtml(order.name)}</strong> | ${escapeHtml(order.phone)}</div>
                <div>地址：${escapeHtml(order.address)}</div>
                <div>预约：${order.date || '未指定'} ${order.time}</div>
                <div>鞋子明细：${order.shoesDetail}</div>
                <div>小计：${order.subtotal}元 → 实付：${order.total}元</div>
                <div>备注：${escapeHtml(order.remark)}</div>
                <div>下单时间：${order.createdAt}</div>
                ${order.photos && order.photos.length ? `<div class="order-photos">${order.photos.map(p => `<img src="${p}">`).join('')}</div>` : ''}
                ${status === 'processing' ? `<div class="photo-upload"><input type="file" multiple accept="image/*" class="photo-input" data-id="${order.id}"><button class="upload-btn" data-id="${order.id}">上传照片</button></div>` : ''}
                ${status === 'pending' ? `<button class="update-status" data-id="${order.id}" data-status="processing">✅ 确认收鞋</button>` : ''}
                ${status === 'processing' ? `<button class="update-status" data-id="${order.id}" data-status="completed">✅ 确认完成</button>` : ''}
                ${status === 'completed' ? `<button class="to-after-sale" data-id="${order.id}">🔧 转为售后</button>` : ''}
                ${status === 'after_sale' ? `<button class="complete-after-sale" data-id="${order.id}">✅ 完成售后</button>` : ''}
            </div>
        `).join('');
        attachOrderEvents();
    }

    function attachOrderEvents() {
        document.querySelectorAll('.update-status').forEach(btn => btn.addEventListener('click', async () => {
            await axios.post(`${apiUrl}?action=updateOrderStatus`, { orderId: btn.dataset.id, status: btn.dataset.status });
            loadOrders();
        }));
        document.querySelectorAll('.to-after-sale').forEach(btn => btn.addEventListener('click', async () => {
            await axios.post(`${apiUrl}?action=updateOrderStatus`, { orderId: btn.dataset.id, status: 'after_sale' });
            loadOrders();
        }));
        document.querySelectorAll('.complete-after-sale').forEach(btn => btn.addEventListener('click', async () => {
            await axios.post(`${apiUrl}?action=updateOrderStatus`, { orderId: btn.dataset.id, status: 'completed' });
            loadOrders();
        }));
        document.querySelectorAll('.upload-btn').forEach(btn => btn.addEventListener('click', async () => {
            const orderId = btn.dataset.id;
            const fileInput = btn.parentElement.querySelector('.photo-input');
            const files = fileInput.files;
            if (!files.length) return;
            const formData = new FormData();
            for (let f of files) formData.append('photos[]', f);
            formData.append('orderId', orderId);
            const res = await axios.post(`${apiUrl}?action=uploadPhoto`, formData);
            if (res.data.success) { alert('上传成功'); loadOrders(); }
            else alert('上传失败');
        }));
    }

    async function clearAllOrders() {
        if (confirm('清空所有订单？')) {
            await axios.get(`${apiUrl}?action=clearOrders`);
            loadOrders();
        }
    }

    function escapeHtml(str) { return str ? str.replace(/[&<>]/g, m => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;' }[m])) : ''; }

    document.getElementById('addCustomBtn').addEventListener('click', () => {
        shoesData.push({ name: '新鞋类', enabled: true, price: 0, isCustom: true });
        renderShoes();
    });
    document.getElementById('saveSettings').addEventListener('click', saveConfig);
    document.getElementById('clearOrders').addEventListener('click', clearAllOrders);
    document.querySelectorAll('.tab').forEach(tab => tab.addEventListener('click', () => {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        renderOrders(tab.dataset.status);
    }));

    loadConfig();
    loadOrders();
    setInterval(loadOrders, 5000);
</script>
</body>
</html>