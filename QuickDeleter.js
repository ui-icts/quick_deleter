$(document).ready(function () {
  let currentRowsChecked = [];

  const url = new URL(window.location.href);
  let pids = url.searchParams.get("pids");

  if (pids !== undefined && pids !== "" && pids !== null) {
    $("#qdCustomPids").val(pids);
    $("#qdSubmitCustom").css({ visibility: "visible" });
  } else {
    $("#qdSubmitCustom").css({ visibility: "hidden" });
  }

  $("#qdCustomPids").on("change keyup keydown paste", function () {
    let isValidCustomPids = false;
    const boxVal = $("#qdCustomPids").val();
    if (boxVal.match(/^\d/)) {
      if (boxVal.includes(",") && !boxVal.endsWith(",")) {
        const boxValSplit = boxVal.split(",");

        for (let i = 0; i < boxValSplit.length; i++) {
          if (
            typeof parseInt(boxValSplit[i]) !== "number" ||
            boxValSplit[i].includes(".") ||
            isNaN(boxValSplit[i])
          ) {
            isValidCustomPids = false;
            break;
          } else {
            isValidCustomPids = true;
          }
        }
      } else if (
        typeof parseInt(boxVal) !== "number" ||
        boxVal.includes(".") ||
        boxVal.endsWith(",")
      ) {
        isValidCustomPids = false;
        // break;
      } else {
        isValidCustomPids = true;
      }
    } else if (boxVal.startsWith("{") && boxVal.endsWith("}")) {
      const pidColumnName = [
        "pid",
        "project-id",
        "project_id",
        "projectid",
        "project id",
      ];
      const jsonizedBoxVal = JSON.parse(boxVal);

      let pidIndex = -1;
      for (let i = 0; i < jsonizedBoxVal.header.length; i++) {
        if (pidColumnName.includes(jsonizedBoxVal.header[i].toLowerCase())) {
          pidIndex = i;

          break;
        }
      }

      let pids = [];

      for (let i = 0; i < jsonizedBoxVal.body.length; i++) {
        pids = [...pids, jsonizedBoxVal.body[i][pidIndex]];
      }

      $("#qdCustomPids").val(pids);
    }

    if (isValidCustomPids) {
      $("#qdSubmitCustom").css({ visibility: "visible" });
    } else {
      $("#qdSubmitCustom").css({ visibility: "hidden" });
    }
  });

  const getQueryData = new URLSearchParams();

  $("#qdSubmitCustom").click(function () {
    pids = $("#qdCustomPids").val();

    const url = `${UIOWA_QD.urlLookup.redcapBase}ExternalModules/?prefix=quick_deleter&page=index&report-id=3&pids=${pids}`;

    document.location.href = url;
  });

  getQueryData.append("report-id", UIOWA_QD.reportId);
  getQueryData.append("redcap_csrf_token", UIOWA_QD.redcap_csrf_token);

  fetchDataAndLoadTable();

  function fetchDataAndLoadTable() {
    getQueryData.append("pids", pids);
    fetch(UIOWA_QD.urlLookup.post, {
      method: "POST",
      body: getQueryData,
    })
      .then((response) => response.text())
      .then((data) => {
        const data2 = data
          .replaceAll("&quot;", '"')
          .replaceAll("<", "&lt;")
          .replaceAll(">", "&gt;");
        let newData = JSON.parse(data2);

        const projectIdLink = {
          data: "Project ID",
          title: "Project ID",
          render: function (data, type, row, meta) {
            return `<a href="${UIOWA_QD.urlLookup.redcapBase}index.php?pid=${row.project_id}" target="_blank">${data}</a>`;
          },
        };

        const projectTitleLink = {
          data: "Project Title",
          title: "Project Title",
          render: function (data, type, row, meta) {
            return `<a href="${UIOWA_QD.urlLookup.redcapBase}ProjectSetup/index.php?pid=${row.project_id}" target="_blank">${data}</a>`;
          },
        };

        const projectStatusLink = {
          data: "Statuses",
          title: "Statuses",
          render: function (data, type, row, meta) {
            return `<a href="${UIOWA_QD.urlLookup.redcapBase}ProjectSetup/other_functionality.php?pid=${row.project_id}" target="_blank">${data}</a>`;
          },
        };

        const projectUsersLink = {
          data: "Users",
          title: "Users",
          render: function (data, type, row, meta) {
            return `<a href="${UIOWA_QD.urlLookup.redcapBase}UserRights/index.php?pid=${row.project_id}" target="_blank">${data}</a>`;
          },
        };

        const projectLastEventLink = {
          data: "Last Event",
          title: "Last Event",
          render: function (data, type, row, meta) {
            return `<a href="${UIOWA_QD.urlLookup.redcapBase}Logging/index.php?pid=${row.project_id}&usr=&record=&beginTime=&endTime=&dag=undefined&logtype=" target="_blank">${data}</a>`;
          },
        };

        const projectDaysSinceEventLink = {
          data: "Days Since Last Event",
          title: "Days Since Last Event",
          render: function (data, type, row, meta) {
            return `<a href="${UIOWA_QD.urlLookup.redcapBase}Logging/index.php?pid=${row.project_id}&usr=&record=&beginTime=&endTime=&dag=undefined&logtype=" target="_blank">${data}</a>`;
          },
        };

        newData.columns.splice(1, 1, projectIdLink);
        newData.columns.splice(2, 1, projectTitleLink);
        newData.columns.splice(5, 1, projectStatusLink);
        newData.columns.splice(7, 1, projectUsersLink);
        newData.columns.splice(8, 1, projectLastEventLink);
        newData.columns.splice(9, 1, projectDaysSinceEventLink);

        let table = $("#qdTable").DataTable({
          data: newData.data,

          scrollXInner: true,
          scrollY: true,
          // stateSave: true, todo - saved sorting can be confusing
          colReorder: true,
          fixedHeader: {
            header: true,
            headerOffset: $("#redcap-home-navbar-collapse").height(),
          },

          columnDefs: [
            {
              targets: 0,
              data: null,

              defaultContent: "",
              orderable: false,
              className: "select-checkbox",
            },
          ],
          select: {
            style: "multi",
            selector: "td:first-child",
          },
          columns: [...newData.columns],
          orderCellsTop: true,
          fixedHeader: true,

          initComplete: function () {
            let $filterRow = $('<tr class="filter-row"></tr>');

            // add column filters
            this.api()
              .columns()
              .every(function () {
                let column = this;
                let $filterTd = $(
                  '<th data-column-index="' + column.index() + '"></th>'
                );
                if (column.index() !== 0) {
                  $filterTd.append('<input style="width: 100%"/>');

                  $("input", $filterTd).on("keyup change clear", function () {
                    if (column.search() !== this.value) {
                      column.search(this.value).draw();
                    }
                  });
                }
                $filterRow.append($filterTd);
              });
            $("div .dataTables_scrollHeadInner thead").append($filterRow);
          },
        });

        // sync filter visibility with column
        table.on("column-visibility.dt", function (e, settings, column, state) {
          let $filterTd = $(".filter-row > td").eq(column);

          state ? $filterTd.show() : $filterTd.hide();
        });

        $('th:contains("Check All"):first').html(
          "<input type='checkbox' id='qdCheckAll'></input>"
        );

        table.on("select", function (e, dt, type, indexes) {
          currentRowsChecked = $.map(
            table.rows(".selected").data(),
            function (item) {
              return item.project_id;
            }
          );

          if (currentRowsChecked.length >= 1) {
            $("#qdReviewSubmit")
              .css({ visibility: "visible" })
              .text(`Review and Submit (${currentRowsChecked.length})`);
          } else {
            $("#qdReviewSubmit").css({ visibility: "hidden" });
          }
        });

        table.on("deselect", function (e, dt, type, indexes) {
          currentRowsChecked = $.map(
            table.rows(".selected").data(),
            function (item) {
              return item.project_id;
            }
          );

          if (currentRowsChecked.length >= 1) {
            $("#qdReviewSubmit")
              .css({ visibility: "visible" })
              .text(`Review and Submit (${currentRowsChecked.length})`);
          } else {
            $("#qdReviewSubmit").css({ visibility: "hidden" });
          }
        });

        $("#qdSubmit").click(function () {
          let ids = $.map(table.rows(".selected").data(), function (item) {
            return item.project_id;
          });

          getQueryData.append("type", "changeStatus");
          getQueryData.append("pids", JSON.stringify(ids));
          fetch(UIOWA_QD.urlLookup.post, {
            method: "POST",
            body: getQueryData,
          })
            .then((response) => response.text())
            .then((data) => {
              window.location.reload();
            });
        });

        $("#qdReviewSubmit").click(function () {
          $(".modal").css({ display: "block" });

          let projectsToDelete = [];
          $(".modal-delete-table").html("");

          let projectsToRestore = [];
          $(".modal-restore-table").html("");

          let selectedPids = $.map(
            table.rows(".selected").data(),
            function (item) {
              return item.project_id;
            }
          );

          for (let i = 0; i < selectedPids.length; i++) {
            for (let j = 0; j < newData.data.length; j++) {
              if (selectedPids[i] === newData.data[j].project_id) {
                if (newData.data[j].date_deleted === null) {
                  projectsToDelete = [
                    ...projectsToDelete,
                    { pid: selectedPids[i], name: newData.data[j].app_title },
                  ];
                } else {
                  projectsToRestore = [
                    ...projectsToRestore,
                    { pid: selectedPids[i], name: newData.data[j].app_title },
                  ];
                }
                break;
              }
            }
          }

          function generateDeleteTableRows() {
            let htmlString = ``;
            for (let i = 0; i < projectsToDelete.length; i++) {
              htmlString += `<tr><td class="tableFormatting deleteTableRow">${projectsToDelete[i].pid}</td><td class="tableFormatting deleteTableRow">${projectsToDelete[i].name}</td></tr>`;
            }
            return htmlString;
          }

          function generateRestoreTableRows() {
            let htmlString = ``;
            for (let i = 0; i < projectsToRestore.length; i++) {
              htmlString += `<tr><td class="tableFormatting restoreTableRow">${projectsToRestore[i].pid}</td><td class="tableFormatting restoreTableRow">${projectsToRestore[i].name}</td></tr>`;
            }
            return htmlString;
          }

          if (projectsToDelete.length >= 1) {
            const deleteTable = `<table class="tableFormatting"><thead><th class="tableFormatting deleteTableHeader">Project ID</th><th class="tableFormatting deleteTableHeader">Project Name</th></thead>    
            <tbody>
                ${generateDeleteTableRows()}
            </tbody>
        </table>`;

            if (projectsToDelete.length >= 1) {
              $(".modal-delete-table").html(
                `DELETE ${projectsToDelete.length} Project(s) ${deleteTable}`
              );
              if (
                projectsToDelete.length >= 1 &&
                projectsToRestore.length >= 1
              ) {
                $("#modal-table-hr").css({ display: "block" });
              } else {
                $("#modal-table-hr").css({ display: "none" });
              }
            }
          }

          const restoreTable = `<table class="tableFormatting">
          <thead>
              <th class="tableFormatting restoreTableHeader">Project ID</th>
              <th class="tableFormatting restoreTableHeader">Project Name</th>
          </thead>    
          <tbody>
              ${generateRestoreTableRows()}
          </tbody>
      </table>`;

          if (projectsToRestore.length >= 1) {
            $(".modal-restore-table").html(
              `${
                projectsToRestore.length >= 1
                  ? `RESTORE ${projectsToRestore.length} Project(s)</br>${restoreTable}`
                  : ``
              } `
            );
          }

          $("#qdSubmit").text(
            `Submit ${
              projectsToDelete.length + projectsToRestore.length
            } Project(s)`
          );
        });

        $(".modal-close").click(function () {
          $(".modal").css({ display: "none" });
        });

        $("body th")
          .on("click", "#qdCheckAll", function () {
            if ($("#qdCheckAll").hasClass("selected")) {
              table.rows().deselect();
              $("#qdCheckAll").removeClass("selected");
            } else {
              table.rows().select();
              $("#qdCheckAll").addClass("selected");
            }
          })
          .on("select deselect", function () {
            ("Some selection or deselection going on");
            if (
              table
                .rows({
                  selected: true,
                })
                .count() !== table.rows().count()
            ) {
              $("#qdCheckAll").removeClass("selected");
            } else {
              $("#qdCheckAll").addClass("selected");
            }
          });
      });
  }
});
