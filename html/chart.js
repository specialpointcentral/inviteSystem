var datasex = {
    datasets: [{
        data: [10, 30],
        backgroundColor: [
            window.chartColors.red,
            window.chartColors.green,
        ],
    }],

    // These labels appear in the legend and in the tooltips when hovering different arcs
    labels: [
        '女生',
        '男生',
    ],
};
var dataPass = {
    datasets: [{
        data: [10,12,13,30],
        backgroundColor: [
            window.chartColors.red,
            window.chartColors.green,
            window.chartColors.purple,
            window.chartColors.blue,
        ],
    }],
    labels: [
        '通过',
        '未通过',
        '异常',
        '待审核',
    ],
};

var post = {
    type: 'line',
    data: {
        labels: ["January", "February", "March", "April", "May", "June", "July"],
        datasets: [{
            label: "申请量",
            backgroundColor: window.chartColors.red,
            borderColor: window.chartColors.red,
            data: [
                11,12,8,6,21,43,21
            ],
            fill: false,
        }, {
            label: "审核量",
            fill: false,
            backgroundColor: window.chartColors.blue,
            borderColor: window.chartColors.blue,
            data: [
                10,15,2,1,3,22,7
            ],
        }]
    },
    options: {
        responsive: true,
        tooltips: {
            mode: 'index',
            intersect: false,
        },
        hover: {
            mode: 'nearest',
            intersect: true
        },
        scales: {
            xAxes: [{
                display: true,
            }],
            yAxes: [{
                display: true,
            }]
        }
    }
};

window.onload = function(){
    var ctxSex = document.getElementById("sex").getContext("2d");
    var chartSex = new Chart(ctxSex,{
        type:'doughnut',
        data:datasex,
    });
    var ctxPass = document.getElementById("pass").getContext("2d");
    var chartPass = new Chart(ctxPass,{
        type:'doughnut',
        data:dataPass,
    });
    var ctxPost = document.getElementById("post").getContext("2d");
    var chartPost = new Chart(ctxPost,post);
}