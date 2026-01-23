"use client";

import { Badge, Table } from "@mantine/core";
import { components } from "../lib/api/schema";

type Article = components["schemas"]["Article.jsonld-article.read.list"];

type Props = {
  articles: Article[];
};

export default function ArticleTable({ articles }: Props) {
  return (
    <>
      <Table mb="lg">
        <Table.Thead>
          <Table.Tr>
            <Table.Th>ID</Table.Th>
            <Table.Th>タイトル</Table.Th>
            <Table.Th>投稿日</Table.Th>
            <Table.Th>公開済み</Table.Th>
          </Table.Tr>
        </Table.Thead>
        <Table.Tbody>
          {articles.map((article) => (
            <Table.Tr key={article.id}>
              <Table.Td>{article.id}</Table.Td>
              <Table.Td>{article.title}</Table.Td>
              <Table.Td>{article.date}</Table.Td>
              <Table.Td>
                {article.published ? (
                  <Badge color="blue">Yes</Badge>
                ) : (
                  <Badge color="gray">No</Badge>
                )}
              </Table.Td>
            </Table.Tr>
          ))}
        </Table.Tbody>
      </Table>
    </>
  );
}
